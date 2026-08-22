/**
 * OpenDXP
 *
 * This source file is licensed under the GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (https://pimcore.com)
 * @copyright  Modification Copyright (c) OpenDXP (https://www.opendxp.ch)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License version 3 (GPLv3)
 */

/**
 * before permissions are loaded
 * context, menu and permissions are passed as parameters
 */
opendxp.events.onPerspectiveEditorLoadPermissions = "opendxp.perspectiveEditor.permissions.load";

/**
 * before permissions structure is loaded
 * context and structure are passed as parameters
 */
opendxp.events.onPerspectiveEditorLoadStructureForPermissions = "opendxp.perspectiveEditor.permissions.structure.load";

/**
 * fired before add elementTreeSettingsForm to perspectiveEditPanel
 * record and perspectiveElementTreeSettingsFormId and setDirtyCallBack are passed as parameters
 */
opendxp.events.preAddPerspectiveEditorElementTreeSettingsForm = "opendxp.perspectiveEditor.elementTreeSettingsForm.preAdd";

/**
 * fired after PerspectiveElementTreeTypeStore was  created
 *  PerspectiveElementTreeTypeStoreId  is passed as parameter
 */
opendxp.events.postCreatePerspectiveEditorElementTreeTypeStore = "opendxp.perspectiveEditor.elementTreeTypeStore.postCreate";

/**
 * fired after ElementTreeIcons Array was  initialized
 */
opendxp.events.addPerspectiveEditorElementTreeIcon = 'opendxp.perspectiveEditor.elementTreeIcon.add';
