<?php
/**
 * @package		HUBzero CMS
 * @author		Alissa Nedossekina <alisa@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$this->css('jquery.datepicker.css', 'system')
	 ->css('jquery.timepicker.css', 'system')
	 ->css()
	 ->js('jquery.timepicker', 'system')
     ->js();

$color = $this->row->get('color');
$class = $color ? 'pin_' . $color : 'pin_grey';

$overdue = $this->row->isOverdue();
$oNote = $overdue ? ' ('.JText::_('PLG_PROJECTS_TODO_OVERDUE').')' : '';

// Can it be deleted?
$deletable = ($this->project->role == 1 or $this->row->get('created_by') == $this->uid) ? 1 : 0;

// Due?
$due = $this->row->due() ? $this->row->due('date') : JText::_('PLG_PROJECTS_TODO_NEVER');

$url = 'index.php?option=' . $this->option . '&alias=' . $this->project->alias . '&active=todo';

$listName = $this->model->getListName($this->project->id, $color);

// How long did it take to complete
if ($this->row->isComplete())
{
	$diff = strtotime($this->row->get('closed')) - strtotime($this->row->get('created'));
	$diff = ProjectsHtml::timeDifference ($diff);
}
$assignee = $this->row->owner('name') ? $this->row->owner('name') : JText::_('PLG_PROJECTS_TODO_NOONE');
?>
<div id="plg-header">
	<h3 class="todo"><a href="<?php echo JRoute::_($url); ?>"><?php echo $this->title; ?></a>
	<?php if ($listName) { ?> &raquo; <a href="<?php echo JRoute::_($url).'/?list=' . $color; ?>"><span class="indlist <?php echo 'pin_' . $color; ?>"><?php echo $listName; ?></span></a> <?php } ?>
	<?php if ($this->row->isComplete()) { ?> &raquo; <span class="indlist completedtd"><a href="<?php echo JRoute::_($url).'/?state=1'; ?>"><?php echo ucfirst(JText::_('PLG_PROJECTS_TODO_COMPLETED')); ?></a></span> <?php } ?>
	&raquo; <span class="itemname"><?php echo \Hubzero\Utility\String::truncate($this->row->get('content'), 60); ?></span>
	</h3>
</div>

<div class="pinboard">
		<section class="section intropage">
			<div class="grid">
				<div class="col span8">
					<div id="td-item" class="<?php echo $class; ?>">
						<span class="pin">&nbsp;</span>
						<div class="todo-content">
							<?php echo $this->row->get('details') ? stripslashes($this->row->get('details')) :  stripslashes($this->row->get('content')); ?>
						</div>
					</div>
				</div>
				<div class="col span4 omega td-details">
					<p><?php echo JText::_('PLG_PROJECTS_TODO_CREATED') . ' ' . $this->row->created('date') .' '.JText::_('PLG_PROJECTS_TODO_BY') . ' ' . $this->row->creator('name'); ?></p>
				<?php if (!$this->row->isComplete()) { ?>
					<p><?php echo JText::_('PLG_PROJECTS_TODO_ASSIGNED_TO') . ' <strong>' . $assignee . '</strong>'; ?></p>
					<p><?php echo JText::_('PLG_PROJECTS_TODO_DUE') . ': <strong>' . $due . '</strong>'; ?></p>
				<?php } else if ($this->row->isComplete()) { ?>
						<p><?php echo JText::_('PLG_PROJECTS_TODO_TODO_CHECKED_OFF') . ' ' . $this->row->closed('date') . ' '.JText::_('PLG_PROJECTS_TODO_BY') . ' ' . ProjectsHtml::shortenName($this->row->closer('name')); ?></p>
						<p><?php echo JText::_('PLG_PROJECTS_TODO_TODO_TOOK') . ' ' . $diff . ' ' . JText::_('PLG_PROJECTS_TODO_TODO_TO_COMPLETE'); ?></p>
				<?php } ?>
				</div>
			</div>
		</section>
	<p class="td-options">
		<?php if (!$this->row->isComplete()) { ?>
		<span class="edit"><a href="<?php echo JRoute::_($url . '&action=edit') . '/?todoid=' . $this->row->get('id'); ?>" class="showinbox"><?php echo JText::_('PLG_PROJECTS_TODO_EDIT'); ?></a></span>
		<span class="checked"><a href="<?php echo JRoute::_($url . '&action=changestate') . '/?todoid=' . $this->row->get('id') . '&amp;state=1'; ?>" class="confirm-checkoff"><?php echo JText::_('PLG_PROJECTS_TODO_TODO_CHECK_OFF'); ?></a></span>
		<?php } ?>
		<?php if ($deletable) { ?>
		<span class="trash"><a href="<?php echo JRoute::_($url . '&action=delete') . '/?todoid=' . $this->row->get('id'); ?>" class="confirm-it" id="deltd"><?php echo JText::_('PLG_PROJECTS_TODO_DELETE'); ?></a></span>
		<?php } ?>
	</p>
	<div class="comment-wrap">
		<h4 class="comment-blurb"><?php echo ucfirst(JText::_('PLG_PROJECTS_TODO_COMMENTS')) . ' (' . $this->row->comments('count') . ')'; ?>:</h4>
		<?php if ($this->row->comments() && $this->row->comments() instanceof \Hubzero\Base\ItemList) { ?>
			<ul id="td-comments">
			<?php foreach ($this->row->comments() as $comment) { ?>
				<li>
					<p><?php echo $comment->content('parsed'); ?></p>
					<p class="todo-assigned"><?php echo $comment->creator('name'); ?> <span class="date"> &middot; <?php echo ProjectsHtml::timeAgo($comment->get('created')).' '.JText::_('PLG_PROJECTS_TODO_AGO'); ?> </span> <?php if ($comment->get('created_by') == $this->uid) { ?><a href="<?php echo JRoute::_($url . '&action=deletecomment').'/?todoid=' . $this->row->get('id') . '&amp;cid=' . $comment->get('id'); ?>" id="delc-<?php echo $comment->get('id'); ?>" class="confirm-it">[<?php echo JText::_('PLG_PROJECTS_TODO_DELETE'); ?>]</a><?php  } ?></p>
				</li>
			<?php } ?>
			</ul>
		<?php } else { ?>
			<p class="noresults"><?php echo ucfirst(JText::_('PLG_PROJECTS_TODO_TODO_NO_COMMENTS')); ?></p>
		<?php } ?>
		<form action="<?php echo JRoute::_($url); ?>" method="post" >
			<div class="addcomment td-comment">
				<label><?php echo ucfirst(JText::_('PLG_PROJECTS_TODO_NEW_COMMENT')); ?>:
					<textarea name="comment" rows="4" cols="50" class="commentarea" id="td-comment" placeholder="<?php echo JText::_('PLG_PROJECTS_TODO_WRITE_COMMENT'); ?>"></textarea>
				</label>
					<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
					<input type="hidden" name="id" value="<?php echo $this->project->id; ?>" />
					<input type="hidden" name="action" value="savecomment" />
					<input type="hidden" name="task" value="view" />
					<input type="hidden" name="active" value="todo" />
					<input type="hidden" name="itemid" value="<?php echo $this->row->get('id'); ?>" />
					<input type="hidden" name="parent_activity" value="<?php echo $this->row->get('activityid'); ?>" />
					<?php echo JHTML::_('form.token'); ?>
					<p class="blog-submit"><input type="submit" class="btn" id="c-submit" value="<?php echo JText::_('PLG_PROJECTS_TODO_ADD_COMMENT'); ?>" /></p>
			</div>
		</form>
	</div>
</div>
