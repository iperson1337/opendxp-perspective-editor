<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules;

use OpenDxp\Event\DataObjectEvents;
use OpenDxp\Event\Model\DataObjectEvent;
use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\Element\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Backend-валидация правил создания объектов: блокирует сохранение объекта
 * недопустимого класса в папке — включая создание через API, импорты и
 * перемещение (drag&drop) в запрещённую папку.
 *
 * UI-фильтрация меню «Добавить объект» — Resources/public/js/opendxp/objectCreationRules/menuFilter.js.
 *
 * Матчер строится лениво и сбрасывается через kernel.reset (ResetInterface,
 * autoconfigure): долгоживущий воркер Messenger сбрасывает сервисы после каждого
 * сообщения и подхватывает правила, сохранённые из UI, без рестарта.
 */
final class CreationGuardSubscriber implements EventSubscriberInterface, ResetInterface
{
    private ?RuleMatcher $ruleMatcher = null;

    /**
     * @param iterable<BypassCheckerInterface> $bypassCheckers
     */
    public function __construct(
        private readonly RulesProvider $rulesProvider,
        private readonly iterable $bypassCheckers = [],
    ) {
    }

    public function reset(): void
    {
        $this->ruleMatcher = null;
    }

    public static function getSubscribedEvents(): array
    {
        // Высокий приоритет (1000) — выполниться ДО остальных подписчиков
        return [
            DataObjectEvents::PRE_ADD => ['onPreAddOrUpdate', 1000],
            DataObjectEvents::PRE_UPDATE => ['onPreAddOrUpdate', 1000],
        ];
    }

    public function onPreAddOrUpdate(DataObjectEvent $event): void
    {
        foreach ($this->bypassCheckers as $bypassChecker) {
            if ($bypassChecker->shouldBypass()) {
                return;
            }
        }

        $object = $event->getObject();
        if (!$object instanceof Concrete) {
            return;
        }

        $parent = $object->getParent();
        if (!$parent) {
            return;
        }

        $folderPath = $parent->getFullPath() ?: '/';
        $className = $object->getClassName();

        // Определяем класс родительского объекта (если родитель - объект, а не папка)
        $parentClassName = null;
        if ($parent instanceof Concrete) {
            $parentClassName = $parent->getClassName();
        }

        $ruleMatcher = $this->ruleMatcher ??= $this->rulesProvider->createMatcher();

        // Проверяем с учётом класса родителя
        if (!$ruleMatcher->isClassAllowed($folderPath, $className, $parentClassName)) {
            $message = $ruleMatcher->getValidationMessage($folderPath, $className, $parentClassName);

            // Останавливаем распространение события, чтобы другие подписчики не обошли валидацию
            $event->stopPropagation();

            throw new ValidationException($message);
        }
    }
}
