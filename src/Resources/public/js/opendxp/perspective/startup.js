/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */


opendxp.registerNS('opendxp.settings.perspectiveview');

opendxp.settings.perspectiveview = Class.create({

    panelId: 'perspective_view_panel_id',

    initialize: function () {
        // if the new event exists, we use this
        if (opendxp.events.preMenuBuild) {
            document.addEventListener(opendxp.events.preMenuBuild, this.createNavigationEntry.bind(this));
        } else {
            document.addEventListener(opendxp.events.opendxpReady, this.createNavigationEntry.bind(this));
        }
    },

    activate: function () {
       Ext.getCmp('opendxp_panel_tabs').setActiveItem(this.getTabPanel());
    },

    createNavigationEntry: function (e) {
        const perspectiveCfg = opendxp.globalmanager.get('perspective');

        if(!perspectiveCfg.inToolbar('settings.perspectiveEditor')){
            return;
        }

        const user = opendxp.globalmanager.get('user');
        if (user.isAllowed('perspective_editor')) {
            const navigationItem = {
                text: t('plugin_opendxp_perspectiveeditor_perspective_view_editor'),
                iconCls: 'opendxp_nav_icon_perspective',
                handler: this.openPerspectiveEditor.bind(this)
            };

            if(e.type === opendxp.events.preMenuBuild){
                let menu = e.detail.menu.settings;

                menu.items.push(navigationItem);
            }

            if(e.type === opendxp.events.opendxpReady){
                let menu = opendxp.globalmanager.get('layout_toolbar').settingsMenu;

                menu.add(navigationItem);
            }
        }
    },

    getTabPanel: function () {
        if (!this.panel) {
            const user = opendxp.globalmanager.get('user');

            this.panel = new Ext.Panel({
                id: this.panelId,
                iconCls: 'opendxp_nav_icon_perspective',
                title: t('plugin_opendxp_perspectiveeditor_perspective_view_editor'),
                border: false,
                layout: 'fit',
                closable: true,
                items: [
                    new Ext.TabPanel({
                        items: [
                            new opendxp.bundle.perspectiveeditor.PerspectiveEditor(),
                            new opendxp.bundle.perspectiveeditor.ViewEditor(!user.isAllowed('perspective_editor_view_edit')),
                            new opendxp.bundle.perspectiveeditor.ObjectCreationRulesEditor(!user.isAllowed('perspective_editor')),
                        ],
                    }),
                ],
            });

            var tabPanel = Ext.getCmp('opendxp_panel_tabs');
            tabPanel.add(this.panel);
            tabPanel.setActiveItem(this.panelId);

            this.panel.on('destroy', function () {
                opendxp.globalmanager.get('plugin_opendxp_perspectiveeditor').panel = false;
                opendxp.globalmanager.remove('plugin_opendxp_perspectiveeditor');
            });

            opendxp.layout.refresh();
        }

        return this.panel;
    },

    addToOpenDxpPanel: function(id){
        opendxp.globalmanager.get(id).on("beforedestroy", function () {
            opendxp.globalmanager.remove(id);
        });

        var opendxpTabPanel = Ext.getCmp("opendxp_panel_tabs");
        opendxpTabPanel.add(opendxp.globalmanager.get(id));
        opendxpTabPanel.setActiveItem(id);

        opendxp.layout.refresh();
    },

    openPerspectiveEditor: function () {
        try{
            opendxp.globalmanager.get('plugin_opendxp_perspectiveeditor').activate();
        } catch (e) {
            this.getTabPanel();
            opendxp.globalmanager.add('plugin_opendxp_perspectiveeditor', settingsPerspectiveView);
        }
    }
});

const settingsPerspectiveView = new opendxp.settings.perspectiveview();
