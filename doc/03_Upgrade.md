# Update Notes

## Migrating from `pimcore/perspective-editor:^1.8` to `open-dxp/perspective-editor-bundle`
* Changed PHP namespace to `OpenDxp\Bundle\PerspectiveEditorBundle`
* Changed Bundle name to `OpenDxpPerspectiveEditorBundle`
* Changed top-level config node to `opendxp_perspective_editor`
* Replaced class-names, translations, labels, etc.:
  * `pimcore` => `opendxp` (yaml identifiers, config keys, translations)
  * `Pimcore` => `OpenDxp` (PHP identifiers, namespaces, classes)
  * `PIMCORE` => `OPENDXP` (PHP constants)
  * `pimcore`(company/vendor) => `open-dxp` (composer package, github/packagist references)
