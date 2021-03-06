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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

if ($this->error) {
	echo '<p class="error">' . Lang::txt('MOD_MYPOINTS_MISSING_TABLE') . '</p>' . "\n";
} else {
	?>
	<div<?php echo ($this->moduleclass) ? ' class="' . $this->moduleclass . '"' : ''; ?>>
		<ul class="module-nav">
			<li>
				<a class="icon-points" href="<?php echo Route::url('index.php?option=com_members&id=' . User::get('id') . '&active=points'); ?>">
					<?php echo Lang::txt('MOD_MYPOINTS_ALL_TRANSACTIONS'); ?>
				</a>
			</li>
		</ul>
		<p class="point-balance">
			<span><?php echo Lang::txt('MOD_MYPOINTS_YOU_HAVE'); ?> </span> <?php echo $this->escape($this->summary); ?><small> <?php echo strtolower(Lang::txt('MOD_MYPOINTS_POINTS')); ?></small>
		</p>
		<?php if (count($this->history) > 0) { ?>
			<table class="transactions">
				<caption><?php echo Lang::txt('MOD_MYPOINTS_TRANSACTIONS_TBL_CAPTION', $this->escape($this->limit)); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php echo Lang::txt('MOD_MYPOINTS_TRANSACTIONS_TBL_TH_DATE'); ?></th>
						<th scope="col"><?php echo Lang::txt('MOD_MYPOINTS_TRANSACTIONS_TBL_TH_TYPE'); ?></th>
						<th scope="col" class="numerical-data"><?php echo Lang::txt('MOD_MYPOINTS_TRANSACTIONS_TBL_TH_AMOUNT'); ?></th>
						<th scope="col" class="numerical-data"><?php echo Lang::txt('MOD_MYPOINTS_TRANSACTIONS_TBL_TH_BALANCE'); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$cls = 'even';
				foreach ($this->history as $item)
				{
					$cls = (($cls == 'even') ? 'odd' : 'even');
					?>
					<tr class="<?php echo $cls; ?>">
						<td>
							<time datetime="<?php echo $this->escape($item->created); ?>"><?php echo Date::of($item->created)->toLocal(Lang::txt('DATE_FORMAT_HZ1')); ?></time>
						</td>
						<td>
							<?php echo $item->type; ?>
						</td>
						<td class="numerical-data">
							<?php if ($item->type == 'withdraw') { ?>
								<span class="withdraw">-<?php echo $this->escape($item->amount); ?></span>
							<?php } elseif ($item->type == 'hold') { ?>
								<span class="hold">(<?php echo $this->escape($item->amount); ?>)</span>
							<?php } else { ?>
								<span class="deposit">+<?php echo $this->escape($item->amount); ?></span>
							<?php } ?>
						</td>
						<td class="numerical-data">
							<?php echo $this->escape($item->balance); ?>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		<?php } ?>
	</div>
	<?php
}