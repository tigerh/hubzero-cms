<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_HZEXEC_') or die();

// Include the component HTML helpers.
Html::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
Html::behavior('framework');
Html::behavior('tooltip');
Html::behavior('formvalidation');
Html::behavior('modal');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task, type)
	{
		if (task == 'item.setType' || task == 'item.setMenuType') {
			if (task == 'item.setType') {
				$('#item-form').find('input[name="jform[type]"]').val(type);
				$('#fieldtype').val('type');
			} else {
				$('#item-form').find('input[name="jform[menutype]"]').val(type);
			}
			Joomla.submitform('item.setType', $('#item-form'));
		} else if (task == 'item.cancel' || document.formvalidator.isValid($('#item-form'))) {
			Joomla.submitform(task, $('#item-form'));
		} else {
			// special case for modal popups validation response
			$('#item-form .modal-value.invalid').each(function(i, field){
				var idReversed = field.id.split("").reverse().join("");
				var separatorLocation = idReversed.indexOf('_');
				var name = idReversed.substr(separatorLocation).split("").reverse().join("")+'name';
				$('#'+name).addClass('invalid');
			});
		}
	}
</script>

<form action="<?php echo Route::url('index.php?option=com_menus&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="grid">
		<div class="col span7">
			<fieldset class="adminform">
				<legend><span><?php echo Lang::txt('COM_MENUS_ITEM_DETAILS');?></span></legend>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('type'); ?>
					<?php echo $this->form->getInput('type'); ?>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('title'); ?>
					<?php echo $this->form->getInput('title'); ?>
				</div>

				<?php if ($this->item->type =='url'): ?>
					<?php $this->form->setFieldAttribute('link', 'readonly', 'false');?>
					<div class="input-wrap">
						<?php echo $this->form->getLabel('link'); ?>
						<?php echo $this->form->getInput('link'); ?>
					</div>
				<?php endif; ?>

				<?php if ($this->item->type == 'alias'): ?>
					<div class="input-wrap">
						<?php echo $this->form->getLabel('aliastip'); ?>
					</div>
				<?php endif; ?>

				<?php if ($this->item->type !='url'): ?>
					<div class="input-wrap">
						<?php echo $this->form->getLabel('alias'); ?>
						<?php echo $this->form->getInput('alias'); ?>
					</div>
				<?php endif; ?>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('note'); ?>
					<?php echo $this->form->getInput('note'); ?>
				</div>

				<?php if ($this->item->type !=='url'): ?>
					<div class="input-wrap">
						<?php echo $this->form->getLabel('link'); ?>
						<?php echo $this->form->getInput('link'); ?>
					</div>
				<?php endif ?>

				<div class="grid">
					<div class="col span6">
						<div class="input-wrap">
							<?php echo $this->form->getLabel('access'); ?>
							<?php echo $this->form->getInput('access'); ?>
						</div>
					</div>
					<div class="col span6">
						<div class="input-wrap">
							<?php echo $this->form->getLabel('published'); ?>
							<?php echo $this->form->getInput('published'); ?>
						</div>
					</div>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('menutype'); ?>
					<?php echo $this->form->getInput('menutype'); ?>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('parent_id'); ?>
					<?php echo $this->form->getInput('parent_id'); ?>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('menuordering'); ?>
					<?php echo $this->form->getInput('menuordering'); ?>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('browserNav'); ?>
					<?php echo $this->form->getInput('browserNav'); ?>
				</div>

				<?php if ($this->item->type == 'component') : ?>
					<div class="input-wrap">
						<?php echo $this->form->getLabel('home'); ?>
						<?php echo $this->form->getInput('home'); ?>
					</div>
				<?php endif; ?>

				<div class="grid">
					<div class="col span6">
						<div class="input-wrap">
							<?php echo $this->form->getLabel('language'); ?>
							<?php echo $this->form->getInput('language'); ?>
						</div>
					</div>
					<div class="col span6">
						<div class="input-wrap">
							<?php echo $this->form->getLabel('template_style_id'); ?>
							<?php echo $this->form->getInput('template_style_id'); ?>
						</div>
					</div>
				</div>

				<div class="input-wrap">
					<?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?>
				</div>
			</fieldset>
		</div>
		<div class="col span5">
			<?php echo Html::sliders('start', 'menu-sliders-'.$this->item->id); ?>
			<?php //Load  parameters.
				echo $this->loadTemplate('options'); ?>

				<div class="clr"></div>

				<?php if (!empty($this->modules)) : ?>
					<?php echo Html::sliders('panel', Lang::txt('COM_MENUS_ITEM_MODULE_ASSIGNMENT'), 'module-options'); ?>
					<fieldset>
						<?php echo $this->loadTemplate('modules'); ?>
					</fieldset>
				<?php endif; ?>

			<?php echo Html::sliders('end'); ?>

			<input type="hidden" name="task" value="" />
			<?php echo $this->form->getInput('component_id'); ?>
			<?php echo Html::input('token'); ?>
			<input type="hidden" id="fieldtype" name="fieldtype" value="" />
		</div>
	</div>
</form>
