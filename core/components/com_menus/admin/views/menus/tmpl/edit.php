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

// No direct access
defined('_HZEXEC_') or die();

// Load the tooltip behavior.
Html::behavior('tooltip');
Html::behavior('formvalidation');
Html::behavior('keepalive');

// Create toolbar
Request::setVar('hidemainmenu', true);

$isNew = ($this->item->id == 0);
$canDo = Components\Menus\Helpers\Menus::getActions();

Toolbar::title(Lang::txt($isNew ? 'COM_MENUS_VIEW_NEW_MENU_TITLE' : 'COM_MENUS_VIEW_EDIT_MENU_TITLE'), 'menu.png');

// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
if ($isNew && $canDo->get('core.create')) {
	if ($canDo->get('core.edit')) {
		Toolbar::apply('apply');
	}
	Toolbar::save('save');
}

// If user can edit, can save the item.
if (!$isNew && $canDo->get('core.edit')) {
	Toolbar::apply('apply');
	Toolbar::save('save');
}

// If the user can create new items, allow them to see Save & New
if ($canDo->get('core.create')) {
	Toolbar::save2new('save2new');
}
if ($isNew) {
	Toolbar::cancel();
} else {
	Toolbar::cancel('cancel', 'JTOOLBAR_CLOSE');
}
Toolbar::divider();
Toolbar::help('menu');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'cancel' || document.formvalidator.isValid($('#item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="item-form">
	<fieldset class="adminform">
		<legend><span><?php echo Lang::txt('COM_MENUS_MENU_DETAILS');?></span></legend>
		<input type="hidden" name="id" value="<?php echo $this->item->get('id'); ?>" />
		<div class="input-wrap">
			<label for="field-title"><?php echo Lang::txt('JGLOBAL_TITLE'); ?>: <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label><br />
			<input type="text" name="fields[title]" id="field-title" size="30" maxlength="48" value="<?php echo $this->escape($this->item->get('title')); ?>" />
			<span class="hint"><?php echo Lang::txt('COM_MENUS_MENU_TITLE_DESC'); ?></span>
		</div>
		<div class="input-wrap">
			<label for="field-menutype"><?php echo Lang::txt('COM_MENUS_MENU_MENUTYPE_LABEL'); ?>: <span class="required"><?php echo Lang::txt('JOPTION_REQUIRED'); ?></span></label><br />
			<input type="text" name="fields[menutype]" id="field-menutype" size="30" maxlength="24" value="<?php echo $this->escape($this->item->get('menutype')); ?>" />
			<span class="hint"><?php echo Lang::txt('COM_MENUS_MENU_MENUTYPE_DESC'); ?></span>
		</div>
		<div class="input-wrap">
			<label for="field-description"><?php echo Lang::txt('JGLOBAL_DESCRIPTION'); ?>:</label><br />
			<input type="text" name="fields[description]" id="field-description" size="30" maxlength="255" value="<?php echo $this->escape($this->item->get('description')); ?>" />
			<span class="hint"><?php echo Lang::txt('COM_MENUS_MENU_DESCRIPTION_DESC'); ?></span>
		</div>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo Request::getCmd('return'); ?>" />
	<?php echo Html::input('token'); ?>
</form>
