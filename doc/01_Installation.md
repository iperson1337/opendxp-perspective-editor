# Installation
## Bundle Installation

1. Install the required dependencies:
```bash
composer require open-dxp/perspective-editor-bundle
```

2. Make sure the bundle is enabled in the `config/bundles.php` file. The following lines should be added:

```php
use OpenDxp\Bundle\PerspectiveEditorBundle\OpenDxpPerspectiveEditorBundle;
// ...

return [
    // ...
    OpenDxpPerspectiveEditorBundle::class => ['all' => true],
    // ...
];
```

3. Install the bundle:

```bash
bin/console opendxp:bundle:install OpenDxpPerspectiveEditorBundle
```

Installation routine just adds an additional permission to `users_permission_definitions` table. 

Also, make sure, that `customviews.php` and `perspectives.php` files are writeable for php.
They can be located at OpenDxp default locations for config files: 
`OPENDXP_CUSTOM_CONFIGURATION_DIRECTORY` or `OPENDXP_CONFIGURATION_DIRECTORY`. 

If they don't exist, they are created at `OPENDXP_CONFIGURATION_DIRECTORY`.
 

## Configuration

To make the bundle work, just make sure, users have the necessary permissions. 
- Perspective Editor: all users with `perspective_editor` permission have all access. 
- Custom Views Editor: all users with `perspective_editor` permission have read access, only admin users
  have write access (due to risk of potential security issues as SQL conditions can be configured in the UI).
  
