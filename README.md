# OpenDXP Perspective Editor Bundle

This bundle provides an editor for OpenDXP to manage custom views and perspectives.

This is the `iperson1337/opendxp-perspective-editor` fork of
[open-dxp/perspective-editor-bundle](https://github.com/open-dxp/perspective-editor-bundle).
It adds features described in [Fork Additions](#fork-additions).

***

## Disclaimer

> OpenDXP is a community-driven fork based on the Pimcore® Community Edition (GPLv3).  
> OpenDXP is independent and maintained by its community and contributors.
> It is not affiliated with, endorsed by, or sponsored by Pimcore GmbH.   
> Original credits: [Pimcore GmbH](https://www.pimcore.com)

**OpenDXP Perspective Editor Bundle is based on the Pimcore® Community Edition and remains licensed under GPLv3.**

***

## Features in a Nutshell
- Possibility to add, remove or edit custom views.
- Possibility to add, remove or edit perspectives.
- Configuration directly in the user interface.

## Fork Additions
- **Object Creation Rules**: an editor tab for per-folder rules that limit which DataObject classes may be
  created where. The rules filter the "Add object" menu and are enforced on save. See
  [Object Creation Rules](./doc/04_Object_Creation_Rules.md).
- **Published objects only** checkbox for object custom views (sets the view `where` condition to `objects.published = 1`).
- Russian admin translations (`admin.ru.yml`).

## Documentation Overview
- [Installation & Configuration](./doc/01_Installation.md)
- [Customize Menu Entry List](./doc/02_Customize_Menu_Entry_List.md)
- [Upgrade Notes](./doc/03_Upgrade.md)
- [Object Creation Rules](./doc/04_Object_Creation_Rules.md)

***

## Upstream Origin & Version Transparency
This package is a fork of [open-dxp/perspective-editor-bundle v1.0.2 (602f18e)](https://github.com/open-dxp/perspective-editor-bundle/tree/602f18e9c8165be01636606e028b1916ce638f86), with the code changes of upstream v1.1.0 (up to 7f77b33) ported in fork release 1.1.0.
That project is itself a fork of the [Pimcore perspective-editor (579aa8a / v1.8.1)](https://github.com/pimcore/perspective-editor/tree/579aa8a6fb63cc66ba62bfdfebf699711739d143), which is © Pimcore GmbH and licensed under GPLv3.

## License
Licensed under the GNU General Public License v3.0 (GPLv3). For details, please see [LICENSE.md](LICENSE.md).

## Copyright
© Pimcore GmbH  
© 2026 OpenDXP Contributors — GPLv3

## Trademarks
Pimcore® is a registered [trademark](https://www.trademarkelite.com/europe/trademark/trademark-detail/009309841/PIMCORE) of Pimcore GmbH.
Any use of the Pimcore® mark in this repository is purely descriptive to identify the original upstream project.

***

## Contact
Questions about this fork: [GitHub issues](https://github.com/iperson1337/opendxp-perspective-editor/issues).
Questions about OpenDXP itself: contact@opendxp.io.

## About
OpenDXP is a community-driven project initiated by [DACHCOM.DIGITAL](https://www.dachcom.com/de-ch) (Rheineck, Switzerland) and maintained by its community and contributors.
OpenDXP is independent and not affiliated with Pimcore GmbH.

The project’s purpose is to preserve and maintain a GPLv3‑licensed codebase for community use.

It is **not positioned as a competitor** to products or services of Pimcore GmbH and does **not** purport to replace or supersede any Pimcore offering.   
