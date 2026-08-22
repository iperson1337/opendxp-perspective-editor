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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('perspective_editor');

        $treeBuilder->getRootNode()
            ->children()
                // Правила создания объектов (сид/фоллбек — до первого сохранения из UI-редактора,
                // после — источником правды становится SettingsStore).
                // Формат правила: см. ObjectCreationRules\RuleMatcher::normalizeRules()
                ->arrayNode('object_creation_rules')
                    ->defaultValue([])
                    ->variablePrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
