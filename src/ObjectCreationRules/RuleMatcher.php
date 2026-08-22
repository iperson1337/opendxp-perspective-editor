<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules;

/**
 * Правила создания объектов: матчинг и валидация — какие классы DataObject
 * можно создавать в какой папке.
 *
 * Чистая логика без I/O (правила приходят в конструктор) — юнит-тестируется без Pimcore,
 * кроме getValidationMessage()/getClassDisplayName(), которые ходят в переводы.
 */
class RuleMatcher
{
    /**
     * @var array<int,array{path:string,allowed_classes:array<int,string>,recursive:bool,depth?:int,parent_class?:string,forbidden_parent_classes?:array<int,string>}>
     */
    private array $rules;

    /**
     * Кэш переводов названий классов (чтобы не делать повторные запросы к БД)
     * @var array<string,string> Маппинг: "City" => "Город"
     */
    private array $translationCache = [];

    /**
     * @param array<int,array{path?:string,allowed_classes?:array<int,string>,recursive?:bool}> $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $this->normalizeRules($rules);
    }

    /**
     * Нормализованные и отсортированные правила
     * @return array<int,array{path:string,allowed_classes:array<int,string>,recursive:bool,depth?:int,parent_class?:string,forbidden_parent_classes?:array<int,string>}>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Находит подходящее правило для папки с учётом класса родителя
     * @return array{path:string,allowed_classes:array<int,string>,recursive:bool,depth?:int,parent_class?:string,forbidden_parent_classes?:array<int,string>}|null
     */
    public function matchRule(string $folderPath, ?string $parentClassName = null): ?array
    {
        $folderPath = rtrim($folderPath, '/') ?: '/';

        foreach ($this->rules as $rule) {
            $rulePath = $rule['path'];

            // Проверка совпадения пути
            $pathMatches = false;
            if ($rule['recursive']) {
                if ($folderPath === $rulePath || str_starts_with($folderPath, $rulePath . '/')) {
                    $pathMatches = true;

                    // Если указана глубина, проверяем её
                    if (isset($rule['depth'])) {
                        $currentDepth = $this->calculateDepth($folderPath, $rulePath);
                        if ($currentDepth !== $rule['depth']) {
                            $pathMatches = false;
                        }
                    }
                }
            } else {
                if ($folderPath === $rulePath) {
                    $pathMatches = true;
                }
            }

            if (!$pathMatches) {
                continue;
            }

            // Если указан parent_class, проверяем совпадение
            if (isset($rule['parent_class'])) {
                if ($parentClassName === null || $parentClassName !== $rule['parent_class']) {
                    continue; // Родительский класс не совпадает
                }
            }

            // Если указан forbidden_parent_classes, проверяем что родитель не в списке запрещённых
            if (isset($rule['forbidden_parent_classes']) && $parentClassName !== null) {
                if (in_array($parentClassName, $rule['forbidden_parent_classes'], true)) {
                    continue; // Родительский класс запрещён
                }
            }

            return $rule;
        }

        return null;
    }

    /**
     * Проверяет, разрешён ли класс в указанной папке с учётом родительского класса
     */
    public function isClassAllowed(string $folderPath, string $className, ?string $parentClassName = null): bool
    {
        $rule = $this->matchRule($folderPath, $parentClassName);

        if ($rule === null) {
            // Нет ограничений - разрешено всё
            return true;
        }

        // Если список пустой - запрещено всё
        if (empty($rule['allowed_classes'])) {
            return false;
        }

        return in_array($className, $rule['allowed_classes'], true);
    }

    /**
     * Генерирует человекочитаемое сообщение об ошибке
     */
    public function getValidationMessage(string $folderPath, string $className, ?string $parentClassName = null): string
    {
        $rule = $this->matchRule($folderPath, $parentClassName);

        if ($rule === null) {
            return ''; // Нет ограничений
        }

        if (empty($rule['allowed_classes'])) {
            return sprintf(
                'В папке "%s" запрещено создавать объекты. Это служебная папка.',
                $folderPath
            );
        }

        if (!in_array($className, $rule['allowed_classes'], true)) {
            $allowedTranslated = array_map(fn($c) => $this->getClassDisplayName($c), $rule['allowed_classes']);
            $allowed = implode(', ', $allowedTranslated);
            $classNameDisplay = $this->getClassDisplayName($className);

            if (isset($rule['parent_class'])) {
                $parentDisplay = $this->getClassDisplayName($rule['parent_class']);
                return sprintf(
                    'В папке "%s" внутри объекта класса "%s" запрещено создавать объект класса "%s". Разрешены: %s.',
                    $folderPath,
                    $parentDisplay,
                    $classNameDisplay,
                    $allowed
                );
            }

            return sprintf(
                'В папке "%s" запрещено создавать объект класса "%s". Разрешены: %s.',
                $folderPath,
                $classNameDisplay,
                $allowed
            );
        }

        return '';
    }

    /**
     * Вычисляет глубину вложенности относительно базового пути
     * Например: '/A/B' относительно '/A' = 1, '/A/B/C' относительно '/A' = 2
     */
    private function calculateDepth(string $currentPath, string $basePath): int
    {
        $currentPath = rtrim($currentPath, '/');
        $basePath = rtrim($basePath, '/');

        if ($currentPath === $basePath) {
            return 0; // Корневой уровень
        }

        $relativePath = substr($currentPath, strlen($basePath) + 1);
        return substr_count($relativePath, '/') + 1;
    }

    /**
     * Получает отображаемое имя класса, как оно показывается в Pimcore UI меню
     * Приоритет:
     * 1) Shared Translations (domain=admin, ru)
     * 2) Title из ClassDefinition (как в UI классов)
     * 3) Техническое имя класса
     */
    private function getClassDisplayName(string $className): string
    {
        // Проверяем кэш переводов
        if (isset($this->translationCache[$className])) {
            return $this->translationCache[$className];
        }

        $displayName = null;

        try {
            // Пытаемся получить перевод из Shared Translations (таблица translations_admin)
            $translation = \OpenDxp\Model\Translation::getByKey($className, 'admin');

            if ($translation) {
                // Получаем русский перевод (используем 'ru' по умолчанию)
                $translatedText = $translation->getTranslation('ru');

                if (!empty($translatedText) && $translatedText !== $className) {
                    // Кладём в кэш и возвращаем
                    return $this->translationCache[$className] = $translatedText;
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки
        }

        // Fallback: пробуем title из ClassDefinition (если задан)
        try {
            $classDefinition = \OpenDxp\Model\DataObject\ClassDefinition::getByName($className);
            if ($classDefinition) {
                $title = trim((string)$classDefinition->getTitle());
                if ($title !== '') {
                    $displayName = $title;
                }
            }
        } catch (\Throwable $e) {
            // Игнорируем ошибки
        }

        // Последний fallback: возвращаем техническое название и тоже кэшируем
        if ($displayName === null || $displayName === '') {
            $displayName = $className;
        }

        return $this->translationCache[$className] = $displayName;
    }

    /**
     * Нормализует и сортирует правила
     * Сортировка: 1) по длине пути (DESC), 2) правила с parent_class выше
     * @param array<int,array{path?:string,allowed_classes?:array<int,string>,recursive?:bool,depth?:int,parent_class?:string,forbidden_parent_classes?:array<int,string>}> $rules
     * @return array<int,array{path:string,allowed_classes:array<int,string>,recursive:bool,depth?:int,parent_class?:string,forbidden_parent_classes?:array<int,string>}>
     */
    private function normalizeRules(array $rules): array
    {
        // Сортируем: сначала по длине пути (DESC), потом правила с parent_class
        usort($rules, static function (array $a, array $b): int {
            $pathLenDiff = strlen((string)($b['path'] ?? '')) <=> strlen((string)($a['path'] ?? ''));
            if ($pathLenDiff !== 0) {
                return $pathLenDiff;
            }

            // При одинаковой длине пути: правила с parent_class имеют приоритет
            $aHasParent = isset($a['parent_class']) ? 1 : 0;
            $bHasParent = isset($b['parent_class']) ? 1 : 0;
            return $bHasParent <=> $aHasParent;
        });

        return array_map(static function (array $r): array {
            $normalized = [
                'path' => rtrim((string)($r['path'] ?? ''), '/') ?: '/',
                'recursive' => (bool)($r['recursive'] ?? false),
                'allowed_classes' => array_values(array_unique(array_map('strval', (array)($r['allowed_classes'] ?? [])))),
            ];

            // Добавляем depth если указано
            if (isset($r['depth'])) {
                $normalized['depth'] = (int)$r['depth'];
            }

            // Добавляем parent_class если указано
            if (isset($r['parent_class'])) {
                $normalized['parent_class'] = (string)$r['parent_class'];
            }

            // Добавляем forbidden_parent_classes если указано
            if (isset($r['forbidden_parent_classes'])) {
                $normalized['forbidden_parent_classes'] = array_values(array_unique(array_map('strval', (array)$r['forbidden_parent_classes'])));
            }

            // Если depth указан, но правило не рекурсивное — depth не применяется
            if (($normalized['recursive'] === false) && isset($normalized['depth'])) {
                unset($normalized['depth']);
            }

            return $normalized;
        }, $rules);
    }
}
