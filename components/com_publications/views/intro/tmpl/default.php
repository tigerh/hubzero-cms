<?php // no direct access
defined('_JEXEC') or die('Restricted access');

?>
<div id="content-header" class="full">
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->
<div class="status-msg">
<?php 
	// Display error or success message
	if ($this->getError()) { 
		echo ('<p class="witherror">' . $this->getError().'</p>');
	}
?>
</div>
<div id="introduction" class="section">
 <div id="introbody">
	<div class="aside">
	</div><!-- / .aside -->
	<div class="subject">
		<?php if ($this->contributable) { ?>
		<div class="two columns first">
		<?php } ?>
			<h3><?php echo JText::_('COM_PUBLICATIONS_WHAT_ARE_THEY'); ?></h3>
			<p><?php echo JText::_('COM_PUBLICATIONS_WHAT_ARE_THEY_EXPLAIN'); ?></p>
			<a href="<?php echo JRoute::_('index.php?option=com_publications'.a.'task=browse'); ?>"><?php echo JText::_('COM_PUBLICATIONS_BROWSE_PUBLICATIONS'); ?> &raquo;</a>
		<?php if ($this->contributable) { ?>
		</div>
		<div class="two columns second">
			<h3><?php echo JText::_('COM_PUBLICATIONS_WHO_CAN_SUBMIT'); ?></h3>
			<p><?php echo JText::_('COM_PUBLICATIONS_WHO_CAN_SUBMIT_ANYONE'); ?></p>
			<p><a href="<?php echo JRoute::_('index.php?option=com_publications'.a.'task=submit'); ?>"><?php echo JText::_('COM_PUBLICATIONS_START_PUBLISHING'); ?> &raquo;</a></p>
		</div>
		<?php } ?>
		<div class="clear"></div>
	</div><!-- / .subject -->
	<div class="clear"></div>
 </div>
</div><!-- / #introduction.section -->

<div class="section">	
	<div class="four columns first">
		<h2><?php echo JText::_('COM_PUBLICATIONS_RECENT_PUBLICATIONS'); ?></h2>
	</div><!-- / .four columns first -->
	<div class="four columns second third fourth">
		<?php if($this->results && count($this->results) > 0) { ?>
			<ul class="mypubs">
				<?php foreach($this->results as $row) {
					// Get version authors
					$pa = new PublicationAuthor( $this->database );
					$authors = $pa->getAuthors($row->version_id); 
					$info = array();
					$info[] =  JHTML::_('date', $row->published_up, '%d %b %Y');	
					$info[] = $row->cat_name;	
					$info[] = JText::_('COM_PUBLICATIONS_CONTRIBUTORS').': '. $this->helper->showContributors( $authors, false, true );
					
					$pubthumb = $this->helper->getThumb($row->id, $row->version_id, $this->config);			
				?>
				<li>
					<span class="pub-thumb"><img src="<?php echo $pubthumb; ?>" alt=""/></span>
					<a href="<?php echo JRoute::_('index.php?option=com_publications'.a.'id='.$row->id);?>" title="<?php echo stripslashes($row->abstract); ?>"><?php echo Hubzero_View_Helper_Html::shortenText(stripslashes($row->title), 60, 0); ?></a>
					<span class="block details"><?php echo implode(' <span>|</span> ',$info); ?></span>
				</li>	
				<?php }?>
			</ul>
		<?php } else { 
			echo ('<p class="noresults">'.JText::_('COM_PUBLICATIONS_NO_RELEVANT_PUBS_FOUND').'</a></p>'); 
		} ?>
	</div><!-- / .four columns second third fourth -->
	<div class="clear"></div>
</div><!-- / .section -->
