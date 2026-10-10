# Object Creation Rules (fork feature)

Per-folder rules that limit which DataObject classes may be created in which part of the object tree.

They act in two places:
- **Admin UI**: the "Add object" context menu of every object tree (the main tree and custom views)
  shows only allowed classes (`Resources/public/js/opendxp/objectCreationRules/menuFilter.js`).
- **Backend**: `ObjectCreationRules\CreationGuardSubscriber` listens to `PRE_ADD` / `PRE_UPDATE`
  (priority 1000) and throws `ValidationException` when an object of a forbidden class is saved
  into a folder. This covers the API, imports and moving objects by drag & drop.

## Where the rules come from

1. Rules saved in the "Object creation rules" tab of the Perspectives Editor are stored in
   `SettingsStore` (scope `object_creation_rules`, id `rules`). The database is shared, so all
   instances see changes at once.
2. Until the rules have been saved from the UI once, the bundle configuration is used as a seed:

```yaml
opendxp_perspective_editor:
    object_creation_rules:
        - path: '/Product'
          allowed_classes: []          # empty list = nothing may be created here
          recursive: false

        - path: '/Product/Catalog'
          allowed_classes: ['Category', 'Product']
          recursive: true
```

The tab is editable for users with the `perspective_editor` permission. The admin context menu picks up
changes after the admin UI is reloaded. Long-running Messenger workers pick them up after the next message.

## Rule fields

| Field | Meaning |
|-------|---------|
| `path` | Folder path, trailing slash is ignored. `/` is the tree root. |
| `allowed_classes` | Class names allowed in the folder. Empty list forbids everything. |
| `recursive` | `false`: only the folder itself. `true`: the folder and all descendants. |
| `depth` | Recursive rules only: match exactly this depth below `path` (`/A/B` is depth 1 for `/A`). |
| `parent_class` | Rule applies only when the parent is an object of this class (e.g. variants under a product). |
| `forbidden_parent_classes` | Rule is skipped when the parent is an object of one of these classes. |

The first matching rule wins. Rules are ordered by path length (longest first); at equal length a rule
with `parent_class` comes first, otherwise the order of the list is kept. A folder without a matching
rule has no restrictions.

## Bypassing the backend check

Bulk operations (imports, message consumers, console commands) may need to skip the check. Implement
`ObjectCreationRules\BypassCheckerInterface` in the application. Implementations are tagged
automatically, and the check is skipped when any of them returns `true`:

```php
use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\BypassCheckerInterface;

final class ImportBypassChecker implements BypassCheckerInterface
{
    public function shouldBypass(): bool
    {
        return ImportGuard::isRunning();
    }
}
```
