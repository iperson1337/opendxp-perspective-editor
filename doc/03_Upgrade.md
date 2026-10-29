# Update Notes

## 1.1.0 (fork `iperson1337/opendxp-perspective-editor`)
Synced with upstream [open-dxp/perspective-editor-bundle v1.1.0](https://github.com/open-dxp/perspective-editor-bundle/blob/1.x/CHANGELOG.md):
* [BUGFIX] `config:dump-reference` and the configuration errors show the key `opendxp_perspective_editor` (the tree root was still named `perspective_editor`; the bundle config key itself was already `opendxp_perspective_editor`, nothing to change in projects).
* [CHORE] The Marketing > SEO menu entry formerly labelled «Robots.txt» is «Site Declarations» — OpenDXP 1.5 serves robots.txt, security.txt and others per site. Only the label changed, the permission key `items.seo.items.robots` stays.
* [CHORE] Require `open-dxp/opendxp` ^1.5.
* Not taken over: upstream's switch from Codeception to Pest (this fork already runs plain PHPUnit), upstream CI workflows and Backstage catalog entry.

## 1.0.3 (fork `iperson1337/opendxp-perspective-editor`)
* [BUGFIX] Object Creation Rules: a recursive rule with path `/` now applies to the whole tree (it matched only `/` itself), backend and "Add object" menu filter.
* [BUGFIX] Object Creation Rules: `CreationGuardSubscriber` reloads the rules after each Messenger message (`kernel.reset`) instead of keeping the rules a worker booted with. Its constructor takes `RulesProvider` instead of `RuleMatcher`.
* [CHORE] Tests run with plain PHPUnit (`vendor/bin/phpunit`); Codeception setup and upstream GitHub workflows removed.

## Migrating from `pimcore/perspective-editor:^1.8` to `open-dxp/perspective-editor-bundle`
* Changed PHP namespace to `OpenDxp\Bundle\PerspectiveEditorBundle`
* Changed Bundle name to `OpenDxpPerspectiveEditorBundle`
* Changed top-level config node to `opendxp_perspective_editor`
* Replaced class-names, translations, labels, etc.:
  * `pimcore` => `opendxp` (yaml identifiers, config keys, translations)
  * `Pimcore` => `OpenDxp` (PHP identifiers, namespaces, classes)
  * `PIMCORE` => `OPENDXP` (PHP constants)
  * `pimcore`(company/vendor) => `open-dxp` (composer package, github/packagist references)
