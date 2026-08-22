# Customize Menu Entry List

Custom view and perspective settings also allow configuring the context menu and toolbar menu entries. 
It might be necessary to add additional entries due to bundles or application code. 

This is possible with following two events:

```javascript
onPerspectiveEditorLoadStructureForPermissions
onPerspectiveEditorLoadPermissions
```

Registering for `onPerspectiveEditorLoadStructureForPermissions` enables you to add a new menu structure, which will then be passed to the `onPerspectiveEditorLoadPermissions` event.

Registering for `onPerspectiveEditorLoadPermissions` enables you to add custom visibility settings for your bundle either to one of the default built-in entries or to your own structure you may have created in the `onPerspectiveEditorLoadStructureForPermissions` event. 

Add one or both events to the `opendxpReady` hook of your bundle. 

Example 1: Add a custom visibility setting to one of the built-in entries:
```javascript
document.addEventListener(opendxp.events.onPerspectiveEditorLoadPermissions, (e) => {
    const context = e.detail.context;
    const menu = e.detail.menu;
    const permissions = e.detail.permissions;
    
    if(context == 'toolbar' && menu == 'search' &&
        permissions[context][menu].indexOf('items.advancedObjectSearch') == -1) {
        permissions[context][menu].push('items.advancedObjectSearch');
    }
}); 
``` 

Example 2: Add your custom structure with custom entries

```javascript
document.addEventListener(opendxp.events.onPerspectiveEditorLoadStructureForPermissions, (e) => {
    if(e.detail.context == 'toolbar') {
        e.detail.structure['customEntry'] = {};
    }
});

document.addEventListener(opendxp.events.onPerspectiveEditorLoadPermissions, (e) => {
    const context = e.detail.context;
    const menu = e.detail.menu;
    const permissions = e.detail.permissions;

    if(context == 'toolbar' && menu == 'customEntry') {
        if(permissions[context][menu] == undefined) {
            permissions[context][menu] = [];
        }
        if(permissions[context][menu].indexOf('hidden') == -1) {
            permissions[context][menu].push('hidden');
        ...
        }
    }
});

``` 

For all available built-in entries please have a look at [MenuItemPermissionHelper](https://github.com/open-dxp/perspective-editor-bundle/blob/1.x/src/Resources/public/js/opendxp/perspective/menuItemPermissionHelper.js).
