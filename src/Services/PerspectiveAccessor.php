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

namespace OpenDxp\Bundle\PerspectiveEditorBundle\Services;

use Exception;
use OpenDxp\Bundle\AdminBundle\Perspective\Config;
use Override;

class PerspectiveAccessor extends AbstractAccessor
{
    protected $filename = 'perspectives.php';

    /**
     * @param array $treeStore
     */
    protected function convertTreeStoreToConfiguration($treeStore): array
    {
        $configuration = [];

        foreach ($treeStore['children'] as $child) {
            $name = htmlspecialchars((string) $child['name']);
            $configuration[$name] = [];
            $configuration[$name]['elementTree'] = [];
            foreach ($child['children'] as $index => $element) {
                if ($element['type'] === 'icon') {
                    $configuration[$name] = array_merge($configuration[$name], $element['config']);
                } elseif ($element['type'] === 'elementTree') {
                    if (isset($element['children'])) {
                        foreach ($element['children'] as $sortIndex => $grandchild) {
                            if (isset($grandchild['config']['treeContextMenu'])) {
                                foreach (array_keys($grandchild['config']['treeContextMenu']) as $contextMenuEntry) {
                                    if (!str_starts_with((string) $grandchild['config']['type'], (string) $contextMenuEntry)) {
                                        unset($grandchild['config']['treeContextMenu'][$contextMenuEntry]);
                                    }
                                }

                                if ($grandchild['config']['treeContextMenu'] === []) {
                                    unset($grandchild['config']['treeContextMenu']);
                                }
                            }
                            $grandchild['config']['sort'] = $sortIndex;
                            $grandchild['config']['position'] = 'left';
                            $configuration[$name]['elementTree'][] = $grandchild['config'];
                        }
                    }
                } elseif ($element['type'] === 'elementTreeRight') {
                    if (isset($element['children'])) {
                        foreach ($element['children'] as $sortIndex => $grandchild) {
                            if (isset($grandchild['config']['treeContextMenu'])) {
                                foreach (array_keys($grandchild['config']['treeContextMenu']) as $contextMenuEntry) {
                                    if (!str_starts_with((string) $grandchild['config']['type'], (string) $contextMenuEntry)) {
                                        unset($grandchild['config']['treeContextMenu'][$contextMenuEntry]);
                                    }
                                }
                                if ($grandchild['config']['treeContextMenu'] === []) {
                                    unset($grandchild['config']['treeContextMenu']);
                                }
                            }
                            $grandchild['config']['sort'] = $sortIndex;
                            $grandchild['config']['position'] = 'right';
                            $configuration[$name]['elementTree'][] = $grandchild['config'];
                        }
                    }
                } elseif ($element['type'] === 'dashboard') {
                    if (count($element['config']) > 0 || isset($element['children'])) {
                        $configuration[$name]['dashboards'] = [];
                    }

                    if (count($element['config']) > 0) {
                        $configuration[$name]['dashboards']['disabledPortlets'] = $element['config'];
                    }

                    if (isset($element['children'])) {
                        foreach ($element['children'] as $dashboardDefinition) {
                            $configuration[$name]['dashboards']['predefined'][$dashboardDefinition['config']['name'] ?? '']['positions'] = $dashboardDefinition['config']['positions'];
                        }
                    }
                } elseif ($element['type'] === 'toolbar') {
                    if (count($element['config']) > 0 || isset($element['children'])) {
                        $configuration[$name]['toolbar'] = $element['config'];
                    }
                }
            }
        }

        return $configuration;
    }

    public function getConfiguration(): array
    {
        return Config::get();
    }

    /**
     * @param array $treeStore
     *
     * @throws Exception
     */
    #[Override]
    public function writeConfiguration($treeStore, ?array $deletedRecords): void
    {
        $configuration = $this->convertTreeStoreToConfiguration($treeStore);
        $this->validateConfig('perspectives', $configuration);
        Config::save($configuration, $deletedRecords);
    }
}
