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

namespace OpenDxp\Bundle\PerspectiveEditorBundle\DependencyInjection;

use OpenDxp\Bundle\PerspectiveEditorBundle\ObjectCreationRules\BypassCheckerInterface;
use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class OpenDxpPerspectiveEditorExtension extends Extension implements PrependExtensionInterface
{
    #[Override]
    public function getAlias(): string
    {
        return 'opendxp_perspective_editor';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'opendxp_perspective_editor.object_creation_rules',
            $config['object_creation_rules'] ?? []
        );

        $container->registerForAutoconfiguration(BypassCheckerInterface::class)
            ->addTag('opendxp_perspective_editor.object_creation_rules.bypass_checker');

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('doctrine_migrations')) {
            $loader = new Loader\YamlFileLoader(
                $container,
                new FileLocator(__DIR__ . '/../Resources/config')
            );

            $loader->load('doctrine_migrations.yml');
        }
    }
}
