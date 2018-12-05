<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author	Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Menus\Helpers;

use Hubzero\Base\Object;
use Hubzero\Access\Access;
use Html;
use Lang;
use User;
use App;

/**
 * Menus component helper
 */
abstract class Menus
{
	/**
	 * Extension name
	 *
	 * @var  string
	 */
	public static $extension = 'com_menus';

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  object
	 */
	public static function getActions()
	{
		$result    = new Object;
		$assetName = self::$extension;

		$actions = Access::getActionsFromFile(\Component::path($assetName) . '/config/access.xml');

		foreach ($actions as $action)
		{
			$result->set($action->name, User::authorise($action->name, 'com_menus'));
		}

		return $result;
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return  array
	 */
	public static function getModules()
	{
		$db = App::get('db');
		$query = $db->getQuery();
		$ag = '#__viewlevels';
		$query->from('#__modules', 'a')
			->select('a.id')
			->select('a.title')
			->select('a.params')
			->select('a.position')
			->whereEquals('a.module', 'mod_menu')
			->select($ag . '.title', 'access_title')
			->join($ag, $ag . '.id', 'a.access');
		$db->setQuery($query->toString());
		$modules = $db->loadObjectList();

		$result = array();

		foreach ($modules as &$module)
		{
			$params = new \Hubzero\Config\Registry($module->params);

			$menuType = $params->get('menutype');
			if (!isset($result[$menuType]))
			{
				$result[$menuType] = array();
			}
			$result[$menuType][] = &$module;
		}

		return $result;
	}
}
