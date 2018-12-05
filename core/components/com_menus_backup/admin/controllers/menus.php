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
 * @author    Tiger Huang <tigerrun1998@gmail.com>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Menus\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Menus\Models\MenuTypes;
use Components\Menus\Models\Menu as MenuModel;
use Components\Menus\Helpers\Menus as MenuHelper;

/**
 * Menus controller class.
 */
class Menus extends AdminController
{
	/**
	 * Determine the task and execute it.
	 *
	 * @return  void
	 */
	public function execute()
	{
		parent::execute();
	}

	public function displayTask()
	{
		$filters = array(
			'limit' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limit',
				'limit',
				\Config::get('list_limit'),
				'int'
			),
			'start' => Request::getState(
				$this->_option . '.' . $this->_controller . '.limitstart',
				'limitstart',
				0,
				'int'
			),
			// Get sorting variables
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'id'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'ASC'
			)
		);

		$query = MenuTypes::all();

		$mt = $query->getTableName();
		$mm = MenuModel::all()->getTableName();

		$query->select($mt . '.id')
			->select($mt . '.menutype')
			->select($mt . '.title')
			->select($mt . '.description');

		$total = $query->count();

		// The subquery method was slightly faster than the left join method when run on the default hub on an EC2 small instance.
		/*
		$query->select('case when ' . $mm . '.published = 1 then 1 end', 'count_published', true)
			->select('case when ' . $mm . '.published = 0 then 1 end', 'count_unpublished', true)
			->select('case when ' . $mm . '.published = -2 then 1 end', 'count_trashed', true)
			->join($mm, $mt . '.menutype', $mm . '.menutype', 'left')
			->group($mm . '.menutype');
		*/
		$query->select('(select count(id) from ' . $mm . ' where ' . $mm . '.menutype = ' . $mt . '.menutype and ' . $mm . '.published = 1)', 'count_published')
			->select('(select count(id) from ' . $mm . ' where ' . $mm . '.menutype = ' . $mt . '.menutype and ' . $mm . '.published = 0)', 'count_unpublished')
			->select('(select count(id) from ' . $mm . ' where ' . $mm . '.menutype = ' . $mt . '.menutype and ' . $mm . '.published = -2)', 'count_trashed');

		$items = $query
			->order($filters['sort'], $filters['sort_Dir'])
			->limit($filters['limit'])
			->start($filters['start'])
			->rows();

		$db = App::get('db');
		$query = $db->getQuery();
		$query->select('e.extension_id')
			->from('#__extensions', 'e')
			->whereEquals('e.type', 'module')
			->whereEquals('e.element', 'mod_menu')
			->whereEquals('e.client_id', 0);
		$db->setQuery($query->toString());
		$modMenuId = $db->loadResult();

		$modules = MenuHelper::getModules();

		$this->view
			->set('filters', $filters)
			->set('items', $items)
			->set('total', $total)
			->set('modMenuId', $modMenuId)
			->set('modules', $modules)
			->display();
	}
}
