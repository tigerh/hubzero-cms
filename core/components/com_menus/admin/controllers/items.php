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

use Hubzero\Base\Object;
use Hubzero\Component\AdminController;
use Components\Menus\Models\MenuType;
use Components\Menus\Models\Menu as MenuModel;
use Components\Menus\Models\MenuType as MenuTypeModel;
use Components\Menus\Helpers\Menus as MenuHelper;

function debug_log($obj, $term = "\n")
{
	file_put_contents('/var/www/hub/dev.log', print_r($obj, true) . $term, FILE_APPEND);
}

/**
 * Items controller class.
 */
class Items extends AdminController
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
	 * Displays a table of menu items
	 */
	public function displayTask()
	{
		// Get filters
		$filters = $this->getFilters();

		// Get query
		$query = $this->getMenus($filters);

		// Get total
		$total = $query->count();

		// Change the start if the start is past the end
		while ($filters['start'] >= $total)
		{
			$filters['start'] -= $filters['limit'];
		}
		if ($filters['start'] < 0)
		{
			$filters['start'] = 0;
		}
		Request::setVar('limitstart', $filters['start']);

		// Get items
		$items = $query
			->order($filters['sort'], $filters['sort_Dir'])
			->paginated('limitstart', 'limit')
			->rows();

		// Preprocess the list of items to find ordering divisions.
		$lang = Lang::getRoot();
		$ordering = array();
		foreach ($items as $item) {
			$ordering[$item->parent_id][] = $item->id;

			// item type text
			switch ($item->type) {
				case 'url':
					$value = Lang::txt('COM_MENUS_TYPE_EXTERNAL_URL');
					break;

				case 'alias':
					$value = Lang::txt('COM_MENUS_TYPE_ALIAS');
					break;

				case 'separator':
					$value = Lang::txt('COM_MENUS_TYPE_SEPARATOR');
					break;

				case 'component':
				default:
					// load language
						$lang->load($item->componentname . '.sys', PATH_APP, null, false, true)
					||	$lang->load($item->componentname . '.sys', PATH_APP . '/components/' . $item->componentname . '/admin', null, false, true)
					||	$lang->load($item->componentname . '.sys', PATH_CORE . '/components/' . $item->componentname . '/admin', null, false, true);

					$componentname = $item->componentname;
					if (!empty($componentname))
					{
						$value = Lang::txt($item->componentname);
						$vars  = null;

						parse_str($item->link, $vars);
						if (isset($vars['view']))
						{
							// Attempt to load the view xml file.
							$file = JPATH_SITE.'/components/'.$item->componentname.'/views/'.$vars['view'].'/metadata.xml';
							if (Filesystem::exists($file) && $xml = simplexml_load_file($file))
							{
								// Look for the first view node off of the root node.
								if ($view = $xml->xpath('view[1]'))
								{
									if (!empty($view[0]['title']))
									{
										$vars['layout'] = isset($vars['layout']) ? $vars['layout'] : 'default';

										// Attempt to load the layout xml file.
										// If Alternative Menu Item, get template folder for layout file
										if (strpos($vars['layout'], ':') > 0)
										{
											// Use template folder for layout file
											$temp = explode(':', $vars['layout']);
											$file = JPATH_SITE.'/templates/'.$temp[0].'/html/'.$item->componentname.'/'.$vars['view'].'/'.$temp[1].'.xml';
											// Load template language file
												$lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE, null, false, true)
											||	$lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE . '/templates/' . $temp[0], null, false, true);

										}
										else
										{
											// Get XML file from component folder for standard layouts
											$file = JPATH_SITE.'/components/'.$item->componentname.'/views/'.$vars['view'].'/tmpl/'.$vars['layout'].'.xml';
										}
										if (Filesystem::exists($file) && $xml = simplexml_load_file($file))
										{
											// Look for the first view node off of the root node.
											if ($layout = $xml->xpath('layout[1]'))
											{
												if (!empty($layout[0]['title']))
												{
													$value .= ' » ' . Lang::txt(trim((string) $layout[0]['title']));
												}
											}
											if (!empty($layout[0]->message[0]))
											{
												$item->item_type_desc = Lang::txt(trim((string) $layout[0]->message[0]));
											}
										}
									}
								}
								unset($xml);
							}
							else
							{
								// Special case for absent views
								$value .= ' » ' . Lang::txt($item->componentname.'_'.$vars['view'].'_VIEW_DEFAULT_TITLE');
							}
						}
					}
					else
					{
						if (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result))
						{
							$value = Lang::txt('COM_MENUS_TYPE_UNEXISTING', $result[1]);
						}
						else
						{
							$value = Lang::txt('COM_MENUS_TYPE_UNKNOWN');
						}
					}
					break;
			}
			$item->item_type = $value;
		}

		$this->view
			->set('filters', $filters)
			->set('items', $items)
			->set('total', $total)
			->set('ordering', $ordering)
			->setLayout('display')
			->display();
	}

	/**
	 * Displays possible menu types
	 *
	 * @return  void
	 */
	public function menutypesTask()
	{
		Request::setVar('hidemainmenu', 1);

		$recordId = Request::getInt('recordId');
		$types = MenuTypeModel::blank()->getTypeOptions();

//		$this->setRedirect(Route::url('index.php?option=com_menus&view=items', false));
		$this->view
			->set('recordId', $recordId)
			->set('types', $types)
			->display();
	}

	/**
	 * Creates a new item
	 *
	 * @return  void
	 */
	public function addTask()
	{
		$this->editTask(null, true);
	}

	/**
	 * Displays the edit form for a single item
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
				$id = Request::getVar('id', Request::getVar('cid', [0])[0]);
			}
			$item = MenuModel::oneOrNew($id);
		}

		$this->view
			->set('item', $item)
			->set('form', $item->getForm())
			->setLayout('edit')
			->display();
	}

	/**
	 * Swap two menu items
	 */
	public function orderSwap($items, $cid1, $cid2, $level)
	{
		// Find mins and maxes
		$state = 0;
		$lft1_min = PHP_INT_MAX;
		$lft1_min_level = 0;
		$lft1_max = -PHP_INT_MAX;
		$lft1_max_level = 0;
		$lft2_min = PHP_INT_MAX;
		$lft2_min_level = 0;
		$lft2_max = -PHP_INT_MAX;
		$lft2_max_level = 0;
		$rgt1_min = PHP_INT_MAX;
		$rgt1_min_level = 0;
		$rgt1_max = -PHP_INT_MAX;
		$rgt1_max_level = 0;
		$rgt2_min = PHP_INT_MAX;
		$rgt2_min_level = 0;
		$rgt2_max = -PHP_INT_MAX;
		$rgt2_max_level = 0;
		foreach ($items as $key => $item)
		{
			$item_level = $item->get('level');
			$item_lft = $item->get('lft');
			$item_rgt = $item->get('rgt');
			// State transitions
			if ($key == $cid1)
			{
				$state = 1;
			}
			elseif ($key == $cid2)
			{
				$state = 2;
			}
			elseif ($state == 2 && $item_level <= $level)
			{
				break;
			}
			// Extract data
			if ($state == 1)
			{
				if ($lft1_min > $item_lft)
				{
					$lft1_min = $item_lft;
					$lft1_min_level = $item_level;
				}
				if ($lft1_max < $item_lft)
				{
					$lft1_max = $item_lft;
					$lft1_max_level = $item_level;
				}
				if ($rgt1_min > $item_rgt)
				{
					$rgt1_min = $item_rgt;
					$rgt1_min_level = $item_level;
				}
				if ($rgt1_max < $item_rgt)
				{
					$rgt1_max = $item_rgt;
					$rgt1_max_level = $item_level;
				}
			}
			elseif ($state == 2)
			{
				if ($lft2_min > $item_lft)
				{
					$lft2_min = $item_lft;
					$lft2_min_level = $item_level;
				}
				if ($lft2_max < $item_lft)
				{
					$lft2_max = $item_lft;
					$lft2_max_level = $item_level;
				}
				if ($rgt2_min > $item_rgt)
				{
					$rgt2_min = $item_rgt;
					$rgt2_min_level = $item_level;
				}
				if ($rgt2_max < $item_rgt)
				{
					$rgt2_max = $item_rgt;
					$rgt2_max_level = $item_level;
				}
			}
		}

		// Compute shifts
		$lft_shift1 = $lft2_max - $lft1_max + $lft1_max_level - $lft2_max_level;
		$lft_shift2 = $lft2_min - $lft1_min;
		$rgt_shift1 = $rgt2_max - $rgt1_max;
		$rgt_shift2 = $rgt2_min - $rgt1_min + $rgt2_min_level - $rgt1_min_level;

		// Shift all values
		$state = 0;
		foreach ($items as $key => $item)
		{
			// State transitions
			if ($key == $cid1)
			{
				$state = 1;
			}
			elseif ($key == $cid2)
			{
				$state = 2;
			}
			elseif ($state == 2 && $item->get('level') <= $level)
			{
				break;
			}
			// Shifting
			if ($state == 1)
			{
				$item->set('lft', $item->get('lft') + $lft_shift1);
				$item->set('rgt', $item->get('rgt') + $rgt_shift1);
			}
			elseif ($state == 2)
			{
				$item->set('lft', $item->get('lft') - $lft_shift2);
				$item->set('rgt', $item->get('rgt') - $rgt_shift2);
			}
		}
	}

	/**
	 * Move item up
	 */
	public function orderupTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Get filters
		$filters = $this->getFilters();

//		// Get the arrays from the Request
//		$order = Request::getArray('order', null, 'post');
//		$originalOrder = explode(',', Request::getString('original_order_values'));
		// Get query
		$query = $this->getMenus($filters, false);

		// Get items
		$total = $query->count();
		$items = $query
			->order($filters['sort'], $filters['sort_Dir'])
			->rows();

		// Find ids
		$cid2 = Request::getInt('cid');
		$level = $items->seek($cid2)->get('level');
		foreach ($items as $key => $item)
		{
			if ($key == $cid2)
			{
				break;
			}
			if ($item->get('level') == $level)
			{
				$cid1 = $key;
			}
		}

		// Perform swap
		$this->orderSwap($items, $cid1, $cid2, $level);
		if (!$items->save())
		{
			Notify::error($items->getError());
			return $this->displayTask();
		}

		// Redirect and success message
		App::redirect(
			Route::url('index.php?option='.$this->_option.'&controller='.$this->_controller.'&task=display', false),
			'Order Up'
		);
	}

	/**
	 * Move item down
	 */
	public function orderDownTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Get filters
		$filters = $this->getFilters();

		// Get query
		$query = $this->getMenus($filters, false);

		// Get items
		$total = $query->count();
		$items = $query
			->order($filters['sort'], $filters['sort_Dir'])
			->rows();

		// Find ids
		$cid1 = Request::getInt('cid');
		$level = $items->seek($cid1)->get('level');
		$state = 0;
		foreach ($items as $key => $item)
		{
			if ($key == $cid1)
			{
				$state = 1;
			}
			elseif ($state == 1 && $item->get('level') == $level)
			{
				$cid2 = $key;
				break;
			}
		}

		// Perform swap
		$this->orderSwap($items, $cid1, $cid2, $level);
		if (!$items->save())
		{
			Notify::error($items->getError());
			return $this->displayTask();
		}

		// Redirect and success message
		App::redirect(
			Route::url('index.php?option='.$this->_option.'&controller='.$this->_controller.'&task=display', false),
			'Order Down'
		);
	}

	/**
	 * Saves reodering
	 */
	public function saveorderTask()
	{
		// Check for request forgeries
		Request::checkToken();

		// Redirect and success message
		App::redirect(
			Route::url('index.php?option='.$this->_option.'&controller='.$this->_controller.'&task=display', false),
			'Save Order'
		);
	}

	/**
	 * Gets a list of filters
	 *
	 * @return  array       $filters			The current filters
	 */
	public function getFilters()
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
			'search' => Request::getState(
				$this->_option . '.' . $this->_controller . '.search',
				'search',
				''
			),
			'menutype' => Request::getState(
				$this->_option . '.' . $this->_controller . '.menutype',
				'menutype',
				'default'
			),
			'level' => Request::getState(
				$this->_option . '.' . $this->_controller . '.level',
				'level',
				''
			),
			'published' => Request::getState(
				$this->_option . '.' . $this->_controller . '.published',
				'published',
				''
			),
			'access' => Request::getState(
				$this->_option . '.' . $this->_controller . '.access',
				'access',
				''
			),
			'language' => Request::getState(
				$this->_option . '.' . $this->_controller . '.language',
				'language',
				''
			),
			'parent_id' => Request::getState(
				$this->_option . '.' . $this->_controller . '.parent_id',
				'parent_id',
				''
			),

//		// Get items to publish from the request.
//		$cid   = Request::getArray('cid', array(), '');
//		$data  = array('setDefault' => 1, 'unsetDefault' => 0);
//		$task  = $this->getTask();
//		$value = \Hubzero\Utility\Arr::getValue($data, $task, 0, 'int');
			// Get sorting variables
			'sort' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sort',
				'filter_order',
				'lft'
			),
			'sort_Dir' => Request::getState(
				$this->_option . '.' . $this->_controller . '.sortdir',
				'filter_order_Dir',
				'asc'
			)
		);
		return $filters;
	}

	/**
	 * Gets a list of all menus
	 *
	 * @param   array       $filters			The current filters
	 * @param   boolean     $modify_published	If the published field should be modified based on type
	 *
	 * @return  relational
	 */
	public function getMenus($filters, $modify_published=true)
	{
		$db = App::get('db');
		$query = MenuModel::all();
		$a = $query->getTableName();

		// Select all fields from the table.
		$query->select($a . '.id')
			->select($a . '.menutype')
			->select($a . '.title')
			->select($a . '.alias')
			->select($a . '.note')
			->select($a . '.path')
			->select($a . '.link')
			->select($a . '.type')
			->select($a . '.parent_id')
			->select($a . '.level')
			->select($a . '.published', 'apublished')
			->select($a . '.component_id')
			->select($a . '.ordering')
			->select($a . '.checked_out')
			->select($a . '.checked_out_time')
			->select($a . '.browserNav')
			->select($a . '.access')
			->select($a . '.img')
			->select($a . '.template_style_id')
			->select($a . '.params')
			->select($a . '.lft')
			->select($a . '.rgt')
			->select($a . '.home')
			->select($a . '.language')
			->select($a . '.client_id');
		if ($modify_published)
		{
			$query->select('CASE ' . $a . '.type' .
				' WHEN ' . $db->quote('component') . ' THEN ' . $a . '.published+2*(' . 'e.enabled-1) ' .
				' WHEN ' . $db->quote('url') . ' THEN ' . $a . '.published+2 ' .
				' WHEN ' . $db->quote('alias') . ' THEN ' . $a . '.published+4 ' .
				' WHEN ' . $db->quote('separator') . ' THEN ' . $a . '.published+6 ' .
				' END', 'published');
		}

		// Join over the language
		$query->select('l.title', 'language_title')
			->select('l.image', 'image')
			->join('`#__languages` AS l', 'l.lang_code', $a . '.language', 'left');

		// Join over the users
		$query->select('u.name', 'editor')
			->join('`#__users` AS u', 'u.id', $a . '.checked_out', 'left');

		// Join over components
		$query->select('c.element', 'componentname')
			->join('`#__extensions` AS c', 'c.extension_id', $a . '.component_id', 'left');

		// Join over the asset groups
		$query->select('ag.title', 'access_level')
			->join('`#__viewlevels` AS ag', 'ag.id', $a . '.access', 'left');

		// Join over the associations
		$assoc = App::has('menu_associations') ? App::get('menu_associations') : 0;
		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 as association')
				->joinRaw('`#__associations` AS asso', 'asso.id = ' . $a . '.id AND asso.context='.$db->quote('com_menus.item'), 'left')
				->join('`#__associations` AS asso2', 'asso2.key', 'asso.key', 'left')
				->group($a . '.id');
		}

		// Join over extensions
		$query->select('e.name', 'name')
			->join('`#__extensions` AS e', 'e.extension_id', $a . '.component_id', 'left');

		// Exclude the root category
		$query->where($a . '.id', '>', 1)
			->whereEquals($a . '.client_id', 0);

		// Filter on the published state
		$published = $filters['published'];
		if (is_numeric($published))
		{
			$query->whereEquals($a . '.published', (int)$published);
		}
		elseif ($published === '')
		{
			$query->whereRaw('(' . $a . '.published IN (0, 1))');
		}

		// Filter by search in title, alias or id
		if ($search = trim($filters['search']))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->whereEquals($a . '.id', (int)substr($search, 3));
			}
			elseif (stripos($search, 'link:') === 0)
			{
				if ($search = substr($search, 5))
				{
					$search = $db->Quote('%'.$db->escape($search, true).'%');
					$query->whereLike($a . '.link', $search);
				}
			}
			else
			{
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->whereRaw('(' . $a . '.title LIKE ' . $search . ' OR ' . $a . '.alias LIKE '.$search.' OR ' . $a . '.note LIKE ' . $search . ')');
			}
		}

		// Filter the items over the parent id if set.
		$parentId = $filters['parent_id'];
		if (!empty($parentId))
		{
			$query->whereEquals('p.id', (int)$parentId);
		}

		// Filter the items over the menu id if set.
		$menuType = $filters['menutype'];
		if (!empty($menuType))
		{
			$query->whereEquals($a . '.menutype', $menuType);
		}

		// Filter on the access level.
		if ($access = $filters['access'])
		{
			$query->whereEquals($a . '.access', (int)$access);
		}

		// Implement View Level Access
		if (!User::authorise('core.admin'))
		{
			$groups	= implode(',', User::getAuthorisedViewLevels());
			$query->whereRaw($a . '.access IN (' . $groups . ')');
		}

		// Filter on the level.
		if ($level = $filters['level'])
		{
			$query->where($a . '.level', '<=', (int)$level);
		}

		// Filter on the language.
		if ($language = $filters['language'])
		{
			$query->whereEquals($a . '.language', $language);
		}

		return $query;
	}
}
