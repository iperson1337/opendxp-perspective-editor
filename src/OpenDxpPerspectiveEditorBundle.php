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

use OpenDxp\Bundle\AdminBundle\OpenDxpAdminBundle;
use OpenDxp\Bundle\PerspectiveEditorBundle\DependencyInjection\OpenDxpPerspectiveEditorExtension;
use OpenDxp\Extension\Bundle\AbstractOpenDxpBundle;
use OpenDxp\Extension\Bundle\Installer\InstallerInterface;
use OpenDxp\Extension\Bundle\OpenDxpBundleAdminClassicInterface;
use OpenDxp\Extension\Bundle\Traits\BundleAdminClassicTrait;
use OpenDxp\Extension\Bundle\Traits\PackageVersionTrait;
use OpenDxp\HttpKernel\Bundle\DependentBundleInterface;
use OpenDxp\HttpKernel\BundleCollection\BundleCollection;
use Override;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class OpenDxpPerspectiveEditorBundle extends AbstractOpenDxpBundle implements OpenDxpBundleAdminClassicInterface, DependentBundleInterface
{
    use BundleAdminClassicTrait;
    use PackageVersionTrait;

    const PERMISSION_PERSPECTIVE_EDITOR = 'perspective_editor';

    const PERMISSION_PERSPECTIVE_EDITOR_VIEW_EDIT = 'perspective_editor_view_edit';

    #[Override]
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new OpenDxpPerspectiveEditorExtension();
        }

        return $this->extension;
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(new OpenDxpAdminBundle(), 60);
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/opendxpperspectiveeditor/js/opendxp/perspective/menuItemPermissionHelper.js',
            '/bundles/opendxpperspectiveeditor/js/opendxp/perspective/perspective.js',
            '/bundles/opendxpperspectiveeditor/js/opendxp/perspective/view.js',
            '/bundles/opendxpperspectiveeditor/js/opendxp/perspective/objectCreationRulesEditor.js',
            '/bundles/opendxpperspectiveeditor/js/opendxp/perspective/common.js',
            '/bundles/opendxpperspectiveeditor/js/opendxp/perspective/startup.js',
            '/bundles/opendxpperspectiveeditor/js/opendxp/perspective/events.js',
            '/bundles/opendxpperspectiveeditor/js/opendxp/objectCreationRules/menuFilter.js',
        ];
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/opendxpperspectiveeditor/css/icons.css',
        ];
    }

    protected function getComposerPackageName(): string
    {
        return 'iperson1337/perspective-editor';
    }

    public function getInstaller(): InstallerInterface
    {
        return $this->container->get(Installer::class);
    }
}
