<?php

declare(strict_types=1);

namespace OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules;

/**
 * Точка расширения: приложение может отключать backend-валидацию правил создания
 * объектов в особых режимах (массовые импорты, обработка Kafka-сообщений, CLI и т.п.),
 * о которых бандл знать не должен.
 *
 * Реализации автоматически получают тег
 * `opendxp_perspective_editor.object_creation_rules.bypass_checker`
 * (autoconfiguration в OpenDxpPerspectiveEditorExtension) и опрашиваются
 * подписчиком перед каждой проверкой: любой `true` — проверка пропускается.
 */
interface BypassCheckerInterface
{
    public function shouldBypass(): bool;
}
