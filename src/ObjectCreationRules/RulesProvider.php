<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules;

use OpenDxp\Model\DataObject\ClassDefinition;
use OpenDxp\Model\Tool\SettingsStore;

/**
 * Источник правил создания объектов.
 *
 * Правила читаются из SettingsStore (если сохранялись из UI-редактора
 * «Правила создания объектов» в Perspectives Editor), иначе — из конфигурации
 * бандла `opendxp_perspective_editor.object_creation_rules` (сид/фоллбек).
 *
 * SettingsStore выбран как хранилище потому, что живёт в БД: изменения из UI
 * сразу видны всем подам в k8s (в отличие от файлов в var/config на локальной ФС пода).
 */
class RulesProvider
{
    public const SETTINGS_STORE_SCOPE = 'object_creation_rules';
    public const SETTINGS_STORE_ID = 'rules';

    public const SOURCE_SETTINGS_STORE = 'settings-store';
    public const SOURCE_CONFIG = 'config';

    /**
     * @param array<int,array<string,mixed>> $fallbackRules правила из конфигурации бандла
     */
    public function __construct(private readonly array $fallbackRules = [])
    {
    }

    /**
     * Действующие правила без нормализации (как сохранены).
     * @return array<int,array<string,mixed>>
     */
    public function getEffectiveRules(): array
    {
        $stored = $this->getStoredRules();

        return $stored ?? $this->fallbackRules;
    }

    /**
     * Откуда взяты действующие правила: self::SOURCE_SETTINGS_STORE | self::SOURCE_CONFIG
     */
    public function getSource(): string
    {
        return $this->getStoredRules() !== null ? self::SOURCE_SETTINGS_STORE : self::SOURCE_CONFIG;
    }

    /**
     * @param array<int,array<string,mixed>> $rules
     */
    public function saveRules(array $rules): void
    {
        SettingsStore::set(
            self::SETTINGS_STORE_ID,
            json_encode(array_values($rules), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'string',
            self::SETTINGS_STORE_SCOPE
        );
    }

    /**
     * Фабрика для DI: RuleMatcher поверх действующих правил
     * (см. Resources/config/services.yml бандла).
     */
    public function createMatcher(): RuleMatcher
    {
        return new RuleMatcher($this->getEffectiveRules());
    }

    /**
     * Нормализованные правила для фронтенда (фильтрация меню «Добавить объект»):
     * к каждому правилу добавлен allowed_class_ids — ID классов (могут отличаться
     * от имён: RetailOutlet -> RO), потому что дерево фильтрует меню по ID класса.
     *
     * @return array<int,array<string,mixed>>
     */
    public function getRulesForFrontend(): array
    {
        $rules = $this->createMatcher()->getRules();

        foreach ($rules as &$rule) {
            $rule['allowed_class_ids'] = array_values(array_filter(array_map(
                fn (string $name): ?string => $this->resolveClassId($name),
                $rule['allowed_classes']
            )));
        }

        return $rules;
    }

    /**
     * @return array<int,array<string,mixed>>|null null = в SettingsStore ничего не сохранено
     */
    private function getStoredRules(): ?array
    {
        $entry = SettingsStore::get(self::SETTINGS_STORE_ID, self::SETTINGS_STORE_SCOPE);
        if ($entry === null) {
            return null;
        }

        $decoded = json_decode((string)$entry->getData(), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function resolveClassId(string $className): ?string
    {
        try {
            return ClassDefinition::getByName($className)?->getId();
        } catch (\Throwable) {
            return null;
        }
    }
}
