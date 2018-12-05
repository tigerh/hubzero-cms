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

use Hubzero\Utility\Arr;
use Hubzero\Component\AdminController;
use Request;
use Notify;
use Route;
use Cache;
use Event;
use Lang;
use App;

/**
 * Menu controller class.
 */
class Menu extends AdminController
{
//	/**
//	 * Dummy method to redirect back to standard controller
//	 *
//	 * @param	boolean			If true, the view output will be cached
//	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
//	 *
//	 * @return	JController		This object to support chaining.
//	 * @since	1.5
//	 */
//	public function display($cachable = false, $urlparams = false)
//	{
//		$this->setRedirect(Route::url('index.php?option=com_menus&view=menus', false));
//	}
//
//	/**
//	 * Method to save a menu item.
//	 *
//	 * @return	void
//	 */
//	public function save($key = null, $urlVar = null)
//	{
//		// Check for request forgeries.
//		Session::checkToken() or exit(Lang::txt('JINVALID_TOKEN'));
//
//		// Initialise variables.
//		$data     = Request::getArray('jform', array(), 'post');
//		$context  = 'com_menus.edit.menu';
//		$task     = $this->getTask();
//		$recordId = Request::getInt('id');
//
//		if (!$this->checkEditId($context, $recordId))
//		{
//			// Somehow the person just went to the form and saved it - we don't allow that.
//			$this->setError(Lang::txt('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
//			$this->setMessage($this->getError(), 'error');
//			$this->setRedirect(Route::url('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));
//
//			return false;
//		}
//
//		// Make sure we are not trying to modify an administrator menu.
//		if ((isset($data['client_id']) && $data['client_id'] == 1) || strtolower($data['menutype']) == 'menu'
//			|| strtolower($data['menutype']) == 'main')
//		{
//			Notify::warning(Lang::txt('COM_MENUS_MENU_TYPE_NOT_ALLOWED'));
//
//			// Redirect back to the edit screen.
//			$this->setRedirect(Route::url('index.php?option=com_menus&view=menu&layout=edit', false));
//
//			return false;
//		}
//
//		// Populate the row id from the session.
//		$data['id'] = $recordId;
//
//		// Get the model and attempt to validate the posted data.
//		$model = $this->getModel('Menu');
//		$form  = $model->getForm();
//		if (!$form)
//		{
//			throw new Exception($model->getError(), 500);
//
//			return false;
//		}
//
//		$data = $model->validate($form, $data);
//
//		// Check for validation errors.
//		if ($data === false)
//		{
//			// Get the validation messages.
//			$errors	= $model->getErrors();
//
//			// Push up to three validation messages out to the user.
//			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
//			{
//				if ($errors[$i] instanceof Exception)
//				{
//					Notify::warning($errors[$i]->getMessage());
//				}
//				else
//				{
//					Notify::warning($errors[$i]);
//				}
//			}
//			// Save the data in the session.
//			User::setState('com_menus.edit.menu.data', $data);
//
//			// Redirect back to the edit screen.
//			$this->setRedirect(Route::url('index.php?option=com_menus&view=menu&layout=edit', false));
//
//			return false;
//		}
//
//		// Attempt to save the data.
//		if (!$model->save($data))
//		{
//			// Save the data in the session.
//			User::setState('com_menus.edit.menu.data', $data);
//
//			// Redirect back to the edit screen.
//			$this->setMessage(Lang::txt('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
//			$this->setRedirect(Route::url('index.php?option=com_menus&view=menu&layout=edit', false));
//
//			return false;
//		}
//
//		$this->setMessage(Lang::txt('COM_MENUS_MENU_SAVE_SUCCESS'));
//
//		// Redirect the user and adjust session state based on the chosen task.
//		switch ($task)
//		{
//			case 'apply':
//				// Set the record data in the session.
//				$recordId = $model->getState($this->context.'.id');
//				$this->holdEditId($context, $recordId);
//
//				// Redirect back to the edit screen.
//				$this->setRedirect(Route::url('index.php?option=com_menus&view=menu&layout=edit'.$this->getRedirectToItemAppend($recordId), false));
//				break;
//
//			case 'save2new':
//				// Clear the record id and data from the session.
//				$this->releaseEditId($context, $recordId);
//				User::setState($context.'.data', null);
//
//				// Redirect back to the edit screen.
//				$this->setRedirect(Route::url('index.php?option=com_menus&view=menu&layout=edit', false));
//				break;
//
//			default:
//				// Clear the record id and data from the session.
//				$this->releaseEditId($context, $recordId);
//				User::setState($context.'.data', null);
}
