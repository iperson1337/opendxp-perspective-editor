/**
 * Вкладка «Правила создания объектов» (форк iperson1337/perspective-editor).
 *
 * Per-folder правила: какие классы DataObject можно создавать в какой папке.
 * Действуют во ВСЕХ деревьях объектов (основном и custom views):
 *  - UI: фильтрация меню «Добавить объект» (../objectCreationRules/menuFilter.js)
 *  - Backend: ObjectCreationRules\CreationGuardSubscriber блокирует сохранение (API, импорты, drag&drop)
 *
 * Хранение: SettingsStore (scope object_creation_rules) через
 * /admin/perspectives-views/object-creation-rules; до первого сохранения действуют
 * правила из конфигурации бандла (opendxp_perspective_editor.object_creation_rules).
 */
opendxp.registerNS('opendxp.bundle.perspectiveeditor.ObjectCreationRulesEditor');

opendxp.bundle.perspectiveeditor.ObjectCreationRulesEditor = class {

    routePrefix = '/admin/perspectives-views/object-creation-rules';

    constructor (readOnly) {
        this.readOnly = readOnly;

        this.rulesStore = new Ext.data.JsonStore({
            autoLoad: true,
            proxy: {
                type: 'ajax',
                url: this.routePrefix + '/get',
                reader: {
                    type: 'json',
                    rootProperty: 'rules'
                }
            },
            fields: [
                {name: 'path', type: 'string', defaultValue: '/'},
                {name: 'allowed_classes', defaultValue: []},
                {name: 'recursive', type: 'boolean', defaultValue: false},
                {name: 'parent_class', type: 'string', defaultValue: ''},
                {name: 'forbidden_parent_classes', defaultValue: []},
                {name: 'depth', defaultValue: null}
            ]
        });

        this.panel = new Ext.Panel({
            title: 'Правила создания объектов',
            iconCls: 'opendxp_icon_folder',
            layout: 'fit',
            items: [this.buildGrid()]
        });

        return this.panel;
    }

    createClassesStore () {
        return new Ext.data.JsonStore({
            autoLoad: true,
            autoDestroy: true,
            proxy: {
                type: 'ajax',
                url: Routing.generate('opendxp_admin_dataobject_class_gettree')
            },
            fields: ['id', 'text']
        });
    }

    createClassTagEditor () {
        return new Ext.form.field.Tag({
            store: this.createClassesStore(),
            valueField: 'text',
            displayField: 'text',
            queryMode: 'local',
            createNewOnEnter: false,
            createNewOnBlur: false,
            filterPickList: true
        });
    }

    classListRenderer (value) {
        return Ext.util.Format.htmlEncode(Ext.isArray(value) ? value.join(', ') : (value || ''));
    }

    buildGrid () {
        const plugins = [];
        if (!this.readOnly) {
            plugins.push(Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 1}));
        }

        const toolbarItems = [];
        if (!this.readOnly) {
            toolbarItems.push(new Ext.Button({
                text: 'Добавить правило',
                iconCls: 'opendxp_icon_plus',
                handler: () => {
                    this.rulesStore.add({
                        path: '/',
                        allowed_classes: [],
                        recursive: false,
                        parent_class: '',
                        forbidden_parent_classes: [],
                        depth: null
                    });
                }
            }));
            toolbarItems.push(new Ext.Button({
                text: 'Удалить выбранное',
                iconCls: 'opendxp_icon_delete',
                handler: () => {
                    const selection = this.grid.getSelectionModel().getSelection();
                    if (selection.length) {
                        this.rulesStore.remove(selection);
                    }
                }
            }));
            toolbarItems.push('-');
            toolbarItems.push(new Ext.Button({
                text: t('save'),
                iconCls: 'opendxp_icon_save',
                handler: this.save.bind(this)
            }));
        }
        toolbarItems.push(new Ext.Button({
            text: t('reload'),
            iconCls: 'opendxp_icon_reload',
            handler: () => this.rulesStore.reload()
        }));
        toolbarItems.push('->');
        toolbarItems.push({
            xtype: 'tbtext',
            text: 'Пустой список классов = в папке запрещено создавать объекты. '
                + 'Правила применяются во всех деревьях; изменения меню — после перезагрузки админки (F5).'
        });

        this.grid = new Ext.grid.Panel({
            store: this.rulesStore,
            plugins: plugins,
            tbar: toolbarItems,
            columnLines: true,
            stripeRows: true,
            columns: [
                {
                    text: 'Путь к папке',
                    dataIndex: 'path',
                    flex: 2,
                    renderer: Ext.util.Format.htmlEncode,
                    editor: this.readOnly ? undefined : {xtype: 'textfield', allowBlank: false, emptyText: '/Товар/Каталог'}
                },
                {
                    text: 'Разрешённые классы',
                    dataIndex: 'allowed_classes',
                    flex: 2,
                    renderer: this.classListRenderer,
                    editor: this.readOnly ? undefined : this.createClassTagEditor()
                },
                {
                    xtype: 'checkcolumn',
                    text: 'Рекурсивно',
                    dataIndex: 'recursive',
                    width: 110,
                    disabled: this.readOnly
                },
                {
                    text: 'Класс родителя',
                    dataIndex: 'parent_class',
                    width: 170,
                    renderer: Ext.util.Format.htmlEncode,
                    editor: this.readOnly ? undefined : new Ext.form.ComboBox({
                        store: this.createClassesStore(),
                        valueField: 'text',
                        displayField: 'text',
                        queryMode: 'local',
                        forceSelection: false,
                        allowBlank: true
                    })
                },
                {
                    text: 'Запрещённые родители',
                    dataIndex: 'forbidden_parent_classes',
                    flex: 1,
                    renderer: this.classListRenderer,
                    editor: this.readOnly ? undefined : this.createClassTagEditor()
                },
                {
                    text: 'Глубина',
                    dataIndex: 'depth',
                    width: 90,
                    renderer: (value) => (value === null || value === undefined || value === '') ? '' : value,
                    editor: this.readOnly ? undefined : {xtype: 'numberfield', allowBlank: true, minValue: 0, allowDecimals: false}
                }
            ]
        });

        return this.grid;
    }

    save () {
        const rules = [];
        this.rulesStore.each(function (record) {
            const depth = record.get('depth');
            rules.push({
                path: record.get('path'),
                allowed_classes: record.get('allowed_classes') || [],
                recursive: !!record.get('recursive'),
                parent_class: record.get('parent_class') || null,
                forbidden_parent_classes: record.get('forbidden_parent_classes') || [],
                depth: (depth === null || depth === undefined || depth === '') ? null : depth
            });
        });

        Ext.Ajax.request({
            url: this.routePrefix + '/save',
            method: 'POST',
            jsonData: {rules: rules},
            success: (response) => {
                const data = Ext.decode(response.responseText);
                if (data.success) {
                    opendxp.helpers.showNotification(t('success'), 'Правила сохранены. Перезагрузите админку (F5), чтобы обновить меню.', 'success');
                    this.rulesStore.reload();
                } else {
                    opendxp.helpers.showNotification(t('error'), data.message || t('error'), 'error');
                }
            },
            failure: (response) => {
                let message = t('error');
                try {
                    message = Ext.decode(response.responseText).message || message;
                } catch (e) {
                    // оставляем дефолтное сообщение
                }
                opendxp.helpers.showNotification(t('error'), message, 'error');
            }
        });
    }
};
