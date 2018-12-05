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
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Menus\Models;

use Hubzero\Config\Registry;
use Hubzero\Database\Relational;
use Hubzero\Form\Form;

/**
 * Menus menu extension model
 */
class Menu extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'menus';

	/**
	 * The table name
	 *
	 * @var  string
	 */
	protected $table = '#__menu';

	/**
	 * Load the language file for the plugin
	 *
	 * @param   boolean  $system  Load the system language file?
	 * @return  boolean
	 */
	public function loadLanguage($system = false)
	{
		$file = $this->get('module') . ($system ? '.sys' : '');
		$paths = array();
		if (substr($this->get('module'), 0, 4) == 'mod_')
		{
			$path = '/modules/' . substr($this->get('module'), 4);
			$paths[] = PATH_APP . $path;
			$paths[] = PATH_CORE . $path;
		}
		$path = '/modules/' . $this->get('module');
		$paths[] = PATH_APP . $path;
		$paths[] = PATH_CORE . $path;
		foreach ($paths as $p)
		{
			if (Lang::load($file, $p, null, false, true))
			{
				return true;
			}
		}
		//return (Lang::load($file, PATH_APP . $path, null, false, true) || Lang::load($file, PATH_CORE . $path, null, false, true));
		return false;
	}

	/**
	 * Get params as a Registry object
	 *
	 * @return  object
	 */
	public function transformParams()
	{
		if (!($this->paramsRegistry instanceof Registry))
		{
			$this->paramsRegistry = new Registry($this->get('params'));
		}
		return $this->paramsRegistry;
	}

	/**
	 * Get a form
	 *
	 * @return  object
	 */
	public function getForm()
	{
		Form::addFieldPath(__DIR__ . '/fields');
		$form = new Form('module', array('control' => 'fields'));
		$file = __DIR__ . '/forms/item.xml';
		$file = Filesystem::cleanPath($file);
		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
		}
		$file = __DIR__ . '/forms/item_alias.xml';
		$file = Filesystem::cleanPath($file);
		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
		}
		$file = __DIR__ . '/forms/item_component.xml';
		$file = Filesystem::cleanPath($file);
		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
		}
		$file = __DIR__ . '/forms/item_seperator.xml';
		$file = Filesystem::cleanPath($file);
		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
		}
		$file = __DIR__ . '/forms/item_url.xml';
		$file = Filesystem::cleanPath($file);
		if (!$form->loadFile($file, false, '//form'))
		{
			$this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
		}
		$paths = array();
		if (substr($this->get('module'), 0, 4) == 'mod_')
		{
			$path = '/modules/' . substr($this->get('module'), 4) . '/' . substr($this->get('module'), 4) . '.xml';
			$paths[] = PATH_APP . $path;
			$paths[] = PATH_CORE . $path;
		}
		$path = '/modules/' . $this->get('module') . '/' . $this->get('module') . '.xml';
		$paths[] = PATH_APP . $path;
		$paths[] = PATH_CORE . $path;
		foreach ($paths as $file)
		{
			if (file_exists($file))
			{
				// Get the plugin form.
				if (!$form->loadFile($file, false, '//config'))
				{
					$this->addError(Lang::txt('JERROR_LOADFILE_FAILED'));
				}
				break;
			}
		}
		$data = $this->toArray();
		$data['params'] = $this->params->toArray();
		$form->bind($data);
		return $form;
	}
}
