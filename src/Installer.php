<?php

/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

namespace OpenDxp\Bundle\PerspectiveEditorBundle;

use OpenDxp\Bundle\PerspectiveEditorBundle\Migrations\Version20211213110000;
use OpenDxp\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use OpenDxp\Model\User\Permission\Definition;
use Override;

class Installer extends SettingsStoreAwareInstaller
{
    #[Override]
    public function needsReloadAfterInstall(): bool
    {
        return true;
    }

    #[Override]
    public function install(): void
    {
        Definition::create(OpenDxpPerspectiveEditorBundle::PERMISSION_PERSPECTIVE_EDITOR);
        Definition::create(OpenDxpPerspectiveEditorBundle::PERMISSION_PERSPECTIVE_EDITOR_VIEW_EDIT);

        parent::install();
    }

    #[Override]
    public function getLastMigrationVersionClassName(): ?string
    {
        return Version20211213110000::class;
    }
}
