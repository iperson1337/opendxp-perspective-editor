/**
 * Per-folder фильтрация классов в контекстном меню «Добавить объект».
 *
 * Правила приходят с сервера в opendxp.settings.objectCreationRules
 * (ObjectCreationRules\AdminSettingsListener; уже нормализованы и отсортированы:
 * специфичные пути выше, при равной длине выше правила с parent_class).
 * Редактируются во вкладке «Правила создания объектов» Perspectives Editor.
 *
 * Механика: opendxp.object.tree читает this.config.allowedClasses при КАЖДОМ
 * открытии контекстного меню (onTreeNodeContextmenu), поэтому мы вычисляем
 * allowedClasses для конкретного узла перед вызовом оригинального метода —
 * дальше работает штатный фильтр дерева (включая меню CSV-импорта).
 * Работает и в основном дереве, и в custom views (они наследуют opendxp.object.tree);
 * собственный список классов custom view используется как база, когда правило
 * для узла не найдено.
 *
 * Это только UI. Серверная валидация — ObjectCreationRules\CreationGuardSubscriber (PRE_ADD/PRE_UPDATE).
 */
(function () {
    'use strict';

    // Ни один реальный класс не имеет такого ID: правило с пустым allowed_classes
    // («запрещено всё») должно отфильтровать все классы, а пустой map в tree.js
    // означает «нет ограничений».
    var NONE_MARKER = '__object_creation_rules_none__';

    function getRules() {
        return (opendxp.settings && opendxp.settings.objectCreationRules) || [];
    }

    function calculateDepth(currentPath, basePath) {
        if (currentPath === basePath) {
            return 0;
        }
        var relative = currentPath.substring(basePath.length + 1);
        return relative.split('/').length;
    }

    // Зеркало App\Service\ObjectCreationRulesService::matchRule()
    function matchRule(rules, folderPath, parentClassName) {
        folderPath = folderPath.replace(/\/+$/, '') || '/';

        for (var i = 0; i < rules.length; i++) {
            var rule = rules[i];
            var rulePath = rule.path;
            var pathMatches = false;

            if (rule.recursive) {
                if (folderPath === rulePath || folderPath.indexOf(rulePath + '/') === 0) {
                    pathMatches = true;
                    if (typeof rule.depth === 'number'
                        && calculateDepth(folderPath, rulePath) !== rule.depth) {
                        pathMatches = false;
                    }
                }
            } else if (folderPath === rulePath) {
                pathMatches = true;
            }

            if (!pathMatches) {
                continue;
            }

            if (rule.parent_class) {
                if (!parentClassName || parentClassName !== rule.parent_class) {
                    continue;
                }
            }

            if (rule.forbidden_parent_classes && parentClassName
                && rule.forbidden_parent_classes.indexOf(parentClassName) !== -1) {
                continue;
            }

            return rule;
        }

        return null;
    }

    function buildAllowedClassesMap(rule) {
        var map = {};
        var ids = rule.allowed_class_ids || [];
        for (var i = 0; i < ids.length; i++) {
            map[ids[i]] = null;
        }
        if (ids.length === 0) {
            map[NONE_MARKER] = null;
        }
        return map;
    }

    function initialize() {
        if (!window.opendxp || !opendxp.object || !opendxp.object.tree) {
            return;
        }

        var original = opendxp.object.tree.prototype.onTreeNodeContextmenu;

        opendxp.object.tree.prototype.onTreeNodeContextmenu = function (tree, record) {
            try {
                if (!this.config.hasOwnProperty('__objectCreationRulesBase')) {
                    this.config.__objectCreationRulesBase = this.config.allowedClasses || null;
                }

                var nodePath = record.data.path;
                var parentClassName = (record.data.type === 'object' && record.data.className)
                    ? record.data.className
                    : null;

                var rule = matchRule(getRules(), nodePath, parentClassName);
                this.config.allowedClasses = rule
                    ? buildAllowedClassesMap(rule)
                    : this.config.__objectCreationRulesBase;
            } catch (err) {
                console.error('[object-creation-rules] menu filter failed:', err);
                this.config.allowedClasses = this.config.__objectCreationRulesBase || null;
            }

            return original.apply(this, arguments);
        };

        // Правило «запрещено всё» оставляет пустой пункт «Добавить объект» — прячем его.
        document.addEventListener(opendxp.events.prepareObjectTreeContextMenu, function (e) {
            var menu = e.detail.menu;
            var record = e.detail.object;
            var parentClassName = (record.data.type === 'object' && record.data.className)
                ? record.data.className
                : null;

            var rule = matchRule(getRules(), record.data.path, parentClassName);
            if (!rule || (rule.allowed_class_ids || []).length > 0) {
                return;
            }

            menu.items.each(function (item) {
                if (item.text === t('add_object')) {
                    item.hide();
                }
            });
        });
    }

    initialize();
})();
