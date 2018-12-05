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
use Components\Menus\Models\MenuType;
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

	/**
	 * Displays a list of menus
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		$filters = array(
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

		$query = MenuType::all();

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
			->paginated('limitstart', 'limit')
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

	/**
	 * Creates a new menu
	 *
	 * @return  void
	 */
	public function addTask()
	{
//		// Check for request forgeries
//		Session::checkToken() or exit(Lang::txt('JINVALID_TOKEN'));
//
//		// Get items to remove from the request.
//		$cid = Request::getArray('cid', array(), '');
		$this->editTask(null, true);
	}

	/**
	 * Edits a menu
	 *
	 * @return  void
	 */
	public function editTask($item=null, $new=false)
	{
		if (!is_object($item))
		{
			if ($new)
			{
				$id = 0;
			}
			else
			{
				$id = Request::getVar('cid', [0])[0];
			}
			$item = MenuType::oneOrNew($id);
		}

//		$this->setRedirect(Route::url('index.php?option=com_menus&view=menus', false));
		$this->view
			->set('item', $item)
			->setLayout('edit')
			->display();
	}

	/**
	 * Saves a menu and goes back to the edit page
	 *
	 * @return  void
	 */
	public function applyTask()
	{
//		Session::checkToken() or exit(Lang::txt('JINVALID_TOKEN'));
//
//		$this->setRedirect(Route::url('index.php?option=com_menus&view=menus', false));
//
//		// Initialise variables.
//		$model = $this->getModel('Item');
		$this->saveTask(false);
	}

	/**
	 * Saves a menu but then creates a new one
	 *
	 * @return  void
	 */
	public function save2NewTask()
	{
		$this->saveTask(false, true);
	}

	/**
	 * Saves a menu
	 *
	 * @param   boolean  $redirect  Redirect the page after saving
	 * @return  void
	 */
	public function saveTask($redirect=true, $redirectNew=false)
	{
		// Check for request forgeries
		Request::checkToken();

		// Incoming
		$id = Request::getVar('id', 0, 'post');
		$fields = Request::getVar('fields', array(), 'post');

		// Create object
		$item = MenuType::oneOrNew($id);
		$item->set($fields);

		if (!$item->save())
		{
			// Something went wrong, return errors
			foreach ($item->getErrors() as $error)
			{
				Notify::error($error);
			}
			return $this->editTask($item);
		}

		if ($redirect)
		{
			// Redirect
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=menus&task=display', false),
				Lang::txt('COM_MENUS_MENU_SAVE_SUCCESS')
			);
			return;
		}
		if ($redirectNew)
		{
			// Redirect
			App::redirect(
				Route::url('index.php?option=' . $this->_option . '&controller=menus&task=add', false),
				Lang::txt('COM_MENUS_MENU_SAVE_SUCCESS')
			);
			return;
		}
		Notify::success(Lang::txt('COM_MENUS_MENU_SAVE_SUCCESS'));
		$this->editTask($item);
	}

	/**
	 * Cancels a task and redirects to the main listing
	 *
	 * @return  void
	 */
	public function cancelTask()
	{
		// Set the redirect
		App::redirect(
			Route::url('index.php?option=' . $this->_option . '&controller=menus&task=display', false)
		);
	}

	/**
	 * Rebuilds the tree
	 *
	 * @return  void
	 */
	public function rebuildTask()
	{
		ddie('rebuild');
	}

	/**
	 * Deletes a menu
	 *
	 * @return  void
	 */
	public function deleteTask()
	{
		ddie('delete');
	}
}
