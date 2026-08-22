<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\Controller;

use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\RulesProvider;
use OpenDxp\Bundle\PerspectiveEditorBundle\OpenDxpPerspectiveEditorBundle;
use OpenDxp\Controller\UserAwareController;
use OpenDxp\Model\DataObject\ClassDefinition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CRUD правил создания объектов для вкладки «Правила создания объектов»
 * (Resources/public/js/opendxp/perspective/objectCreationRulesEditor.js).
 *
 * Хранение — SettingsStore через RulesProvider; до первого сохранения из UI
 * действуют правила из конфигурации бандла (object_creation_rules).
 */
class ObjectCreationRulesController extends UserAwareController
{
    public function __construct(private readonly RulesProvider $rulesProvider)
    {
    }

    #[Route('/object-creation-rules/get', name: 'opendxp_perspective_editor_objectcreationrules_get', methods: ['GET'])]
    public function getAction(): JsonResponse
    {
        $this->checkPermission(OpenDxpPerspectiveEditorBundle::PERMISSION_PERSPECTIVE_EDITOR);

        return new JsonResponse([
            'success' => true,
            'rules' => $this->rulesProvider->getEffectiveRules(),
            'source' => $this->rulesProvider->getSource(),
        ]);
    }

    #[Route('/object-creation-rules/save', name: 'opendxp_perspective_editor_objectcreationrules_save', methods: ['POST'])]
    public function saveAction(Request $request): JsonResponse
    {
        $this->checkPermission(OpenDxpPerspectiveEditorBundle::PERMISSION_PERSPECTIVE_EDITOR);

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload) || !is_array($payload['rules'] ?? null)) {
            return new JsonResponse(['success' => false, 'message' => 'Ожидается JSON вида {"rules": [...]}'], 400);
        }

        $errors = [];
        $normalized = [];
        foreach (array_values($payload['rules']) as $i => $rule) {
            $normalized[] = $this->normalizeRule((array)$rule, $i + 1, $errors);
        }

        if ($errors !== []) {
            return new JsonResponse(['success' => false, 'message' => implode("\n", $errors)], 400);
        }

        $this->rulesProvider->saveRules($normalized);

        return new JsonResponse([
            'success' => true,
            'rules' => $this->rulesProvider->getEffectiveRules(),
            'source' => $this->rulesProvider->getSource(),
        ]);
    }

    /**
     * @param array<string,mixed> $rule
     * @param list<string> $errors
     * @return array<string,mixed>
     */
    private function normalizeRule(array $rule, int $position, array &$errors): array
    {
        $path = trim((string)($rule['path'] ?? ''));
        if ($path === '' || !str_starts_with($path, '/')) {
            $errors[] = sprintf('Правило #%d: путь должен начинаться с "/" (получено: "%s")', $position, $path);
        }
        $path = rtrim($path, '/') ?: '/';

        $allowedClasses = $this->normalizeClassList($rule['allowed_classes'] ?? [], $position, 'allowed_classes', $errors);

        $normalized = [
            'path' => $path,
            'allowed_classes' => $allowedClasses,
            'recursive' => (bool)($rule['recursive'] ?? false),
        ];

        $parentClass = trim((string)($rule['parent_class'] ?? ''));
        if ($parentClass !== '') {
            $this->assertClassExists($parentClass, $position, 'parent_class', $errors);
            $normalized['parent_class'] = $parentClass;
        }

        $forbidden = $this->normalizeClassList($rule['forbidden_parent_classes'] ?? [], $position, 'forbidden_parent_classes', $errors);
        if ($forbidden !== []) {
            $normalized['forbidden_parent_classes'] = $forbidden;
        }

        if (isset($rule['depth']) && $rule['depth'] !== '' && $rule['depth'] !== null) {
            $depth = (int)$rule['depth'];
            if ($depth < 0) {
                $errors[] = sprintf('Правило #%d: depth не может быть отрицательным', $position);
            }
            $normalized['depth'] = $depth;
        }

        return $normalized;
    }

    /**
     * @param list<string> $errors
     * @return list<string>
     */
    private function normalizeClassList(mixed $value, int $position, string $field, array &$errors): array
    {
        $classes = array_values(array_unique(array_filter(array_map(
            static fn ($v): string => trim((string)$v),
            (array)$value
        ), static fn (string $v): bool => $v !== '')));

        foreach ($classes as $className) {
            $this->assertClassExists($className, $position, $field, $errors);
        }

        return $classes;
    }

    /**
     * @param list<string> $errors
     */
    private function assertClassExists(string $className, int $position, string $field, array &$errors): void
    {
        try {
            $exists = ClassDefinition::getByName($className) !== null;
        } catch (\Throwable) {
            $exists = false;
        }

        if (!$exists) {
            $errors[] = sprintf('Правило #%d: класс "%s" (%s) не существует', $position, $className, $field);
        }
    }
}
