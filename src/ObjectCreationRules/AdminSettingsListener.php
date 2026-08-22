<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules;

use OpenDxp\Bundle\AdminBundle\Event\AdminEvents;
use OpenDxp\Bundle\AdminBundle\Event\IndexActionSettingsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Внедряет действующие правила создания объектов в opendxp.settings.
 * Их читает Resources/public/js/opendxp/objectCreationRules/menuFilter.js,
 * который фильтрует классы в контекстном меню «Добавить объект» per-folder
 * во всех деревьях объектов (основном и custom views).
 */
class AdminSettingsListener implements EventSubscriberInterface
{
    public function __construct(private readonly RulesProvider $rulesProvider)
    {
    }

    public static function getSubscribedEvents(): array
    {
        // ВАЖНО: именно константа ('opendxp.admin.indexAction.settings').
        // Старая самописная система слушала несуществующее
        // 'opendxp.admin.index_action_settings' и потому не работала.
        return [
            AdminEvents::INDEX_ACTION_SETTINGS => 'onIndexSettings',
        ];
    }

    public function onIndexSettings(IndexActionSettingsEvent $event): void
    {
        $event->addSetting('objectCreationRules', $this->rulesProvider->getRulesForFrontend());
    }
}
