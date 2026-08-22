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
use InvalidArgumentException;
use Override;

class ViewAccessor extends AbstractAccessor
{
    protected $filename = 'customviews.php';

    /**
     * @return array
     */
    public function getAvailableViews()
    {
        $configuration = $this->getConfiguration();
        $availableViews = [];

        if ($configuration) {
            foreach ($configuration['views'] as $view) {
                $availableViews[] = ['id' => $view['id'], 'name' => $view['name'] . ' (Type: '. $view['treetype'] .', Root: '. $view['rootfolder'] .')'];
            }
        }

        return $availableViews;
    }

    /**
     * @param array $treeStore
     *
     * @return array
     */
    protected function convertTreeStoreToConfiguration($treeStore)
    {
        $configuration = [];

        if (isset($treeStore['children'])) {
            foreach ($treeStore['children'] as $child) {
                if (array_key_exists('name', $child['config'])) {
                    $child['config']['name'] = htmlspecialchars((string) $child['config']['name']);
                }

                if (!empty($child['config']['treeContextMenu'])) {
                    foreach (array_keys($child['config']['treeContextMenu']) as $contextMenuEntry) {
                        if (!str_starts_with((string) $child['config']['treetype'], (string) $contextMenuEntry)) {
                            unset($child['config']['treeContextMenu'][$contextMenuEntry]);
                        }
                    }
                }
                $configuration[$child['id']] = $child['config'];
            }
        }

        return $configuration;
    }

    public function getConfiguration(): array
    {
        $views = \OpenDxp\Bundle\AdminBundle\CustomView\Config::get();

        if ($views) {
            foreach ($views as $key => $view) {
                if (isset($views[$key]['classes'])) {
                    $views[$key]['classes'] = array_keys($view['classes']);
                }
            }

            return ['views' => $views];
        }

        return [];
    }

    /**
     * @return void
     */
    protected function verifySql(array $configuration)
    {
        foreach ($configuration as $viewConfiguration) {
            foreach ([$viewConfiguration['having'] ?? '', $viewConfiguration['where'] ?? ''] as $sql) {
                if (preg_match('/(ALTER|CREATE|DROP|RENAME|TRUNCATE|UPDATE|DELETE|SET) /i', $sql, $matches)) {
                    throw new InvalidArgumentException('Invalid SQL definition, possible SQL injection?');
                }
            }
        }
    }

    /**
     * @param array $treeStore
     *
     * @return void
     *
     * @throws Exception
     */
    #[Override]
    public function writeConfiguration($treeStore, ?array $deletedRecords)
    {
        $configuration = $this->convertTreeStoreToConfiguration($treeStore);
        $this->verifySql($configuration);
        $this->validateConfig('custom_views', $configuration);
        \OpenDxp\Bundle\AdminBundle\CustomView\Config::save($configuration, $deletedRecords);
    }
}
