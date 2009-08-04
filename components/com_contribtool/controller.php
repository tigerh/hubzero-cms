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

class ContribtoolController extends JObject
{
	private $_name  = NULL;
	private $_data  = array();
	private $_task  = NULL;
	private $_error = NULL;

	//-----------

	public function __construct( $config=array() )
	{
		$this->_redirect = NULL;
		$this->_message = NULL;
		$this->_messageType = 'message';

		// Set the controller name
		if (empty( $this->_name )) {
			if (isset($config['name'])) {
				$this->_name = $config['name'];
			} else {
				$r = null;
				if (!preg_match('/(.*)Controller/i', get_class($this), $r)) {
					echo "Controller::__construct() : Can't get or parse class name.";
				}
				$this->_name = strtolower( $r[1] );
			}
		}

		// Set the component name
		$this->_option = 'com_'.$this->_name;
	}

	//-----------

	public function setVar ($property, $value)
	{
		$this->$property = $value;
	}
	
	//-----------

	public function getVar ($property)
	{
		return $this->$property;
	}
	//-----------

	public function __set($property, $value)
	{
		$this->_data[$property] = $value;
	}

	//-----------

	public function __get($property)
	{
		if (isset($this->_data[$property])) {
			return $this->_data[$property];
		}
	}
	//-----------

	private function getStyles($option='')
	{
		ximport('xdocument');
		if ($option) {
			XDocument::addComponentStylesheet($option);
		} else {
			XDocument::addComponentStylesheet($this->_option);
		}

		XDocument::addComponentStylesheet('com_support');
		//XDocument::addComponentStylesheet('com_contribute');
	}

	//-----------

	private function getScripts($option='',$name='')
	{
		$document =& JFactory::getDocument();
		if ($option) {
			$name = ($name) ? $name : $option;
			if (is_file(JPATH_ROOT.DS.'components'.DS.'com_'.$option.DS.$name.'.js')) {
				$document->addScript('components'.DS.'com_'.$option.DS.$name.'.js');
			}
		} else {
			if (is_file(JPATH_ROOT.DS.'components'.DS.$this->_option.DS.$this->_name.'.js')) {
				$document->addScript('components'.DS.$this->_option.DS.$this->_name.'.js');
			}
		}
	}


	//-----------

	private function getTask()
	{
		$task = JRequest::getVar( 'task', '', 'post' );
		if (!$task) {
			$task = JRequest::getVar( 'task', '', 'get' );
		}
		if(!$task) {
			$task = 'pipeline';
		}
		$juser =& JFactory::getUser();
		if ($juser->get('guest')) {
			$task = 'login';
		}
		$this->_task = $task;

		return $task;
	}
	//-----------

	public function execute()
	{
		// Get the component parameters
		$tconfig = new ContribtoolConfig( $this->_option );
		$this->config = $tconfig;
		
		$contribtool_enabled = (isset($this->config->parameters['contribtool_on'])) ? $this->config->parameters['contribtool_on'] : 0;
		
		if(!$contribtool_enabled) {
			// Redirect to home page
			$this->_redirect = '/home/';
			$this->redirect();		
		}
		
		
		// Load the com_resources component config
		$rconfig =& JComponentHelper::getParams( 'com_resources' );
		$this->rconfig = $rconfig;

		switch( $this->getTask() )
		{
		case 'login':       	$this->login();       			break;
		case 'pipeline': 		$this->summary(); 		 		break;
		case 'status':  		$this->status();  				break;
		case 'register': 		$this->save();					break;
		case 'edit': 			$this->edit();					break;
		case 'save':  			$this->save();  				break;
		case 'update': 			$this->save();					break;
		case 'message': 		$this->save();					break;
		case 'create':			$this->new_tool(); 				break;
		case 'cancel':			$this->cancel(); 				break;

		// admin actions
		case 'publishtool': 	$this->save();  				break;
		case 'installtool': 	$this->save();  				break;
		case 'createtool': 		$this->save();  				break;
		case 'retiretool': 		$this->save();  				break;
		
		// versioning
		case 'versions':		$this->version();				break;
		case 'saveversion': 	$this->save();  				break;
		case 'finalizeversion': $this->save();  				break;
		// licensing
		case 'license':			$this->license();				break;
		case 'savelicense': 	$this->save();  				break;

		// resource page editing functions
		case 'start':   	 	$this->edit_resource();			break;
		case 'preview': 	 	$this->preview_resource();		break;

		// managing attachments
		case 'rename':       	$this->attach_rename();  		break;
		case 'saveattach':   	$this->attach_save();    		break;
		case 'deleteattach': 	$this->attach_delete();  		break;
		case 'attach':       	$this->attachments();    		break;
		case 'orderupa':     	$this->reorder_attach(); 		break;
		case 'orderdowna':   	$this->reorder_attach(); 		break;
		
		// managing screenshots
		case 'screenshots':		$this->screenshots();			break;
		case 'uploadss':		$this->ss_upload();				break;
		case 'deletess':		$this->ss_delete();				break;
		case 'editss':			$this->ss_edit();				break;
		case 'savess':			$this->ss_save();				break;
		case 'orderss':     	$this->ss_reorder(); 			break;
		
		// managing contributors	
		case 'saveauthor':   	$this->author_save();    		break;
		case 'removeauthor': 	$this->author_remove();  		break;
		case 'authors':      	$this->authors();        		break;
		case 'orderupc':     	$this->reorder_author(); 		break;
		case 'orderdownc':   	$this->reorder_author(); 		break;
		
		//case 'test':   		$this->test(); 					break;	
		case 'movess':   		$this->movess(); 				break;
		case 'copyss':   		$this->copyss(); 				break;

		default: 			 	$this->summary(); 				break;
		}
	}

	//-----------

	public function redirect()
	{
		if ($this->_redirect != NULL) {
			$app =& JFactory::getApplication();
			$app->redirect( $this->_redirect, $this->_message, $this->_messageType );
		}
	}
	
	//-----------

	public function testdoi()
	{
		// temp test function for doi handle creation
		$database =& JFactory::getDBO();
		$objDOI = new ResourcesDoi ($database);	
		
		$url = 'https://www3.nanohub.org';
		$handle = 'nanohub-test123';
		//$doiservice = isset($this->config->parameters['doi_service']) ? $this->config->parameters['doi_service'] : 'http://dir1.lib.purdue.edu:8080/axis/services/CreateHandleService?wsdl';
		
		$doiservice = 'http://dir3.lib.purdue.edu:8080/axis/services/DeleteHandleService?wsdl';
		
		$objDOI->deleteDOIHandle($url, $handle, $doiservice);
		
	}

	//----------------------------------------------------------
	// Views
	//----------------------------------------------------------

	protected function login($msg='') 
	{
		// Set the page title
		$title = JText::_(strtoupper($this->_name)).': '.JText::_(strtoupper($this->_task));
		
		$document =& JFactory::getDocument();
		$document->setTitle( $title );
		
		$japp =& JFactory::getApplication();
		$pathway =& $japp->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		$pathway->addItem( JText::_(strtoupper($this->_task)), 'index.php?option='.$this->_option.a.'task='.$this->_task );
		
		echo ContribtoolHtml::hed( 2, $title );
		if($msg) { echo ContribtoolHtml::warning( $msg ); }
		ximport('xmodule');
		XModuleHelper::displayModules('force_mod');
	
	}


	//-----------

	protected function version()
	{
		$database =& JFactory::getDBO();
		$juser =& JFactory::getUser();

		// get admin priviliges
		$this->authorize_admin();

		// get vars
		if (!$this->_toolid) {
			$this->_toolid = JRequest::getInt( 'toolid', 0 );
		}
		if (!$this->_action) {
			$this->_action = JRequest::getVar( 'action', 'dev');
		}
		if (!$this->_error) {
			$this->_error = JRequest::getVar( 'error', '');
		}
		
		$ldap = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;

		// check access rights
		if($this->check_access($this->_toolid, $juser, $this->_admin) ) {

			// Create a Tool object
			$obj = new Tool( $database );

			// get tool status
			$obj->getToolStatus( $this->_toolid, $this->_option, $status, 'dev', $ldap );

			if(!$status) {
				JError::raiseError( 404, JText::_('ERR_STATUS_CANNOT_FIND') );
				return;
			}
			
			// Create a Tool Version object
			$objV = new ToolVersion( $database );
			$objV->getToolVersions( $this->_toolid, $versions, '', $ldap); 
			
		}
		else {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// add the CSS and JS
		$this->getStyles();
		$this->getScripts();
		
		// Set the page title
		$title = JText::_(strtoupper($this->_name)).': ';
		$title .= ($this->_action=='confirm') ? JText::_('CONTRIBTOOL_APPROVE_TOOL') : JText::_('TASK_VERSIONS');
		//$title .= ' ('.$status['toolname'].')';
		$document =& JFactory::getDocument();
		$document->setTitle( $title );
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_('STATUS').' '.JText::_('FOR').' '.$status['toolname'], 'index.php?option='.$this->_option.a.'task=status'.a.'toolid='.$this->_toolid );
			if($this->_action!='confirm') {
			$pathway->addItem( JText::_('TASK_VERSIONS'), 'index.php?option='.$this->_option.a.'task=versions'.a.'toolid='.$this->_toolid );
			}
		}
		
		
		echo ContribtoolHtml::writeToolVersions($versions, $juser, $status, $this->_admin, $this->_error, $this->_option, $this->_action, $title);

	}

	//-----------

	protected function finalize_version ()
	{
		$database =& JFactory::getDBO();
		$juser =& JFactory::getUser();

		// get admin priviliges
		$this->authorize_admin();

		// get vars
		if (!$this->_toolid) {
			$this->_toolid = JRequest::getInt( 'toolid', 0 );
		}
		if (!$this->_error) {
			$this->_error = JRequest::getVar( 'error', '');
		}
		
		$ldap = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		
		// check access rights
		if($this->check_access($this->_toolid, $juser, $this->_admin) ) {

			// Create a Tool object
			$obj = new Tool( $database );

			// get tool status
			$obj->getToolStatus( $this->_toolid, $this->_option, $status, 'dev', $ldap );

			if(!$status) {
				JError::raiseError( 404, JText::_('ERR_STATUS_CANNOT_FIND') );
				return;
			}
		}
		else {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}
		
		/// add the CSS to the template and set the page title
		$this->getStyles();
		$this->getScripts();
		
		// Set the page title
		$title = JText::_(strtoupper($this->_name)).': '.JText::_('CONTRIBTOOL_APPROVE_TOOL');
		//$title .= ' ('.$status['toolname'].')';
		$document =& JFactory::getDocument();
		$document->setTitle( $title );
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_('STATUS').' '.JText::_('FOR').' '.$status['toolname'], 'index.php?option='.$this->_option.a.'task=status'.a.'toolid='.$this->_toolid );
		}
		
		echo ContribtoolHtml::writeFinalizeVersion($status, $this->_admin, $this->_error, $this->_option, $title);

	}

	//-----------

	protected function license()
	{
		$database =& JFactory::getDBO();
		$juser =& JFactory::getUser();

		// get admin priviliges
		$this->authorize_admin();

		// get vars
		if (!$this->_toolid) {
			$this->_toolid = JRequest::getInt( 'toolid', 0 );
		}
		if (!$this->_action) {
			$this->_action = JRequest::getVar( 'action', 'dev');
		}
		if (!$this->_error) {
			$this->_error = JRequest::getVar( 'error', '');
		}
		
		$ldap = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;


		// check access rights
		if($this->check_access($this->_toolid, $juser, $this->_admin) ) {

			// Create a Tool object
			$obj = new Tool( $database );

			// get tool status
			$obj->getToolStatus( $this->_toolid, $this->_option, $status, 'dev', $ldap );

			if(!$status) {
				JError::raiseError( 404, JText::_('ERR_STATUS_CANNOT_FIND') );
				return;
			}
		}
		else {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}
		
		// get license
		if (!$this->license_choice) {
			$this->license_choice = array('text'=>$status['license'], 'template'=>'c1');
		}
	
		if(!$this->code) {
			$this->code = $status['code'];
		}
		
		// get default license text
		$toolhelper = new ContribtoolHelper();
		$licenses = $toolhelper->getLicenses($database);

		/// add the CSS to the template and set the page title
		$this->getStyles();
		$this->getScripts();

		// Set the page title
		$title  = JText::_(strtoupper($this->_name)).': ';
		$title .= ($this->_action=='confirm') ? JText::_('CONTRIBTOOL_APPROVE_TOOL') : JText::_('TASK_LICENSE');
		//$title .= ' ('.$status['toolname'].')';
		$document =& JFactory::getDocument();
		$document->setTitle( $title );
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_('STATUS').' '.JText::_('FOR').' '.$status['toolname'], 'index.php?option='.$this->_option.a.'task=status'.a.'toolid='.$this->_toolid );
			if($this->_action!='confirm') {
			$pathway->addItem( JText::_('TASK_LICENSE'), 'index.php?option='.$this->_option.a.'task=license'.a.'toolid='.$this->_toolid );
			}
		}
		
		echo ContribtoolHtml::writeToolLicense($licenses, $status, $this->_admin, $this->_error, $this->_option, $this->_action, $this->license_choice, $this->code, $this->_action, $title);

	}

	//-----------

	protected function summary ()
	{
		$database 	=& JFactory::getDBO();
		$xuser 		=& XFactory::getUser();
		$juser     	=& JFactory::getUser();

		// get admin priviliges
		$this->authorize_admin();

		// Set the page title
		$title = JText::_(strtoupper($this->_name)).': '.JText::_(strtoupper($this->_task));
		$document =& JFactory::getDocument();
		$document->setTitle( $title );

		// Incoming
		$filters = $this->getFilters($this->_admin);

		// Create a Tool object
		$obj = new Tool( $database );

		// Record count
		$total = $obj->getToolCount( $filters, $this->_admin);

		// Fetch results
		$rows = $obj->getTools( $filters, $this->_admin);

		// Initiate paging class
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $filters['start'], $filters['limit'] );


		// Get some needed styles
		$this->getStyles();
		$this->getScripts();
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		

		echo ContribtoolHtml::summary ($rows, $this->_option, $filters, $this->_admin, $pageNav, $total, $title, $this->config);

	}

	//-----------

	protected function status()
	{
		$xuser 	   	=& XFactory::getUser();
		$juser     	=& JFactory::getUser();
		$database 	=& JFactory::getDBO();
		$xhub      	=& XFactory::getHub();

		// get admin priviliges
		$this->authorize_admin();

		if (!$this->_toolid) {
			$this->_toolid = JRequest::getInt( 'toolid', 0 );
		}
		
		// Create a Tool object
		$obj = new Tool( $database );
		
		// do we have an alias?
		if($this->_toolid == 0) {
			$alias = JRequest::getVar( 'alias', '');
			if($alias) {
				$this->_toolid = $obj->getToolId($alias);
			}
		}
		
		// Couldn't get ID, exit
		if (!$this->_toolid) {
			//JError::raiseError( 404, JText::_('ERR_STATUS_CANNOT_FIND') );
			$this->_redirect = JRoute::_('index.php?option='.$this->_option);
			return;
		}

		if (!$this->_error) {
			$this->_error = '';
		}
		if (!$this->_msg) {
			$this->_msg = JRequest::getVar( 'msg', '', 'post' );
		}

		$ldap = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;

		// check access rights
		if($this->check_access($this->_toolid, $juser, $this->_admin) ) {

			// get tool status
			$obj->getToolStatus( $this->_toolid, $this->_option, $status, 'dev', $ldap );

			if(!$status) {
				JError::raiseError( 404, JText::_('ERR_STATUS_CANNOT_FIND') );
				return;
			}
			
			// get tickets/wishes/questions
			if($status['published']) {
						// get open questions
						require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_answers'.DS.'answers.class.php' );
						$aq = new AnswersQuestion( $database );	
						$filters = array();
						$filters['filterby'] = 'all';
						$filters['sortby']   = 'date';
						$filters['tag']  	 = 'tool'.$status['toolname'];
						$status['questions'] = $aq->getCount( $filters );
						
						
						// get open wishes
						$database->setQuery("SHOW TABLES");
						$tables = $database->loadResultArray();
						
						if ($tables && array_search($database->_table_prefix.'wishlist', $tables)===false) {
							// Wishlist table not found!
							$status['wishes'] = 'NA';
						}
						else {
											
							require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_wishlist'.DS.'wishlist.wishlist.php' );
							require_once( JPATH_ROOT.DS.'components'.DS.'com_wishlist'.DS.'controller.php' );
							
							$objWishlist = new Wishlist( $database );
							$objWish = new Wish( $database );
							$listid = $objWishlist->get_wishlistID($status['resourceid'], 'resource');
							if($listid) {
								$filters = WishlistController::getFilters(1);
								$wishes = $objWish->get_wishes($listid, $filters, 1, $juser);
								$status['wishes'] = count($wishes);
							}
							else {
								$status['wishes']= 0;
							}
						}
			}
			

		}
		else {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Set the page title
		$title = ''.JText::_(strtoupper($this->_name)).': '.JText::_(strtoupper($this->_task));
		$title .= $status['toolname'] ? ' '.JText::_('FOR').' '.$status['toolname'] : '';
		$document =& JFactory::getDocument();
		$document->setTitle( $title );

		// Get some needed styles
		$this->getStyles();
		$this->getScripts();
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_(strtoupper($this->_task)).' '.JText::_('FOR').' '.$status['toolname'], 'index.php?option='.$this->_option.a.'task=status'.a.'toolid='.$this->_toolid );
		}

		echo ContribtoolHtml::writeToolStatus($status, $xuser, $this->_admin, $this->_error, $this->_option, $this->_msg, $title, $this->config);
	}

	//-----------

	protected function new_tool()
	{
		$database  =& JFactory::getDBO();
		$juser     =& JFactory::getUser();

		// get admin priviliges
		$this->authorize_admin();

		// set defaults
		list($vncGeometryX, $vncGeometryY) = split('[x]', $this->config->parameters['default_vnc']);

		$defaults = array('toolname' => 'shortname',
						  'title' => '',
						  'version' => '1.0',
						  'description' => '',
						  'exec' => '',
						  'membergroups' => array(),
						  'published' => '',
						  'code' => '',
						  'wiki' => '',
						  'developers' => array($juser->get('uid')),
						  'vncGeometryX' => $vncGeometryX,
						  'vncGeometryY' => $vncGeometryY,
						  'team' => $juser->get('username') );

		// Set the page title
		$title = JText::_(strtoupper($this->_name)).': '.JText::_('TASK_CREATE_NEW_TOOL');
		$document =& JFactory::getDocument();
		$document->setTitle( $title );

		// Get some needed styles
		$this->getStyles();
		$this->getScripts();
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_('TASK_CREATE_NEW_TOOL'), 'index.php?option='.$this->_option.a.'task=create' );
		}

		echo ContribtoolHtml::writeToolForm($this->_option, $title, $this->_admin, $juser, $defaults, $err=array(), $id='', $this->_task, $this->config );
	}

	//-------------

	protected function edit()
	{
		$database =& JFactory::getDBO();
		$xuser 	  =& XFactory::getUser();
		$juser    =& JFactory::getUser();
		$xhub      =& XFactory::getHub();

		// get admin priviliges
		$this->authorize_admin();

		if (!$this->_toolid) {
			$this->_toolid = JRequest::getInt( 'toolid', 0 );
		}
		$editversion = JRequest::getVar( 'editversion', '');
		$editversion = ($editversion == 'current') ? 'current' : 'dev'; // do not allow to edit all versions just yet, will default to dev
		
		
		$err=array();

		// check access rights
		if($this->check_access($this->_toolid, $juser, $this->_admin, 0) ) {

			// Create a Tool object
			$obj = new Tool( $database );

			// get tool status
			$obj->getToolStatus( $this->_toolid, $this->_option, $status, $editversion );

			if(!$status) {
				JError::raiseError( 404, JText::_('ERR_EDIT_CANNOT_FIND') );
				return;
			}
		}
		else {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Set the page title
		$title = JText::_(strtoupper($this->_name)).': '.JText::_('TASK_EDIT_TOOL');
		$document =& JFactory::getDocument();
		$document->setTitle( $title );

		// Get some needed styles
		$this->getStyles();
		$this->getScripts();
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_('STATUS').' '.JText::_('FOR').' '.$status['toolname'], 'index.php?option='.$this->_option.a.'task=status'.a.'toolid='.$this->_toolid );
			$pathway->addItem( JText::_('TASK_EDIT_TOOL'), 'index.php?option='.$this->_option.a.'task=edit'.a.'toolid='.$this->_toolid );
		}

		echo ContribtoolHtml::writeToolForm($this->_option, $title, $this->_admin, $juser, $status, $err, $this->_toolid, $this->_task, $this->config, $editversion);
	}

	//----------------------------------------------------------
	// Process
	//----------------------------------------------------------

	protected function save()
	{
		$database 	=& JFactory::getDBO();
		$xuser 		=& XFactory::getUser();
		$juser 	   	=& JFactory::getUser();
		$task  	    = $this->_task;

		// get admin priviliges
		$this->authorize_admin();
		
		// set vars
		$tool				= ($task=='save' or $task=='register') ? array_map('trim', $_POST['tool']): array();
		$today 				= date( 'Y-m-d H:i:s', time() );
		$ldap_read 			= isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		$ldap_save		    = isset($this->config->parameters['ldap_save']) ? $this->config->parameters['ldap_save'] : 0;
		$group_prefix       = isset($this->config->parameters['group_prefix']) ? $this->config->parameters['group_prefix'] : 'app-';
		$dev_siffix       	= isset($this->config->parameters['dev_suffix']) ? $this->config->parameters['dev_suffix'] : '_dev';
		$invokedir 			= isset($this->config->parameters['invokescript_dir']) ? $this->config->parameters['invokescript_dir'] : DS.'apps';

		if (!$this->_error) {
			$this->_error = '';
		}
		if (!$this->_msg) {
			$this->_msg = '';
		}

		// Get some needed styles
		$this->getStyles();
		$this->getScripts();


		// pass data from forms
		$id 			= JRequest::getInt( 'id', '');
		$this->_action 	= JRequest::getVar( 'action', '');
		$comment 		= JRequest::getVar( 'comment', '');
		$editversion 	= JRequest::getVar( 'editversion', 'dev','post');
		$toolname 		= ($task=='save' or $task=='register') ? strtolower($tool['toolname']) : strtolower(JRequest::getVar( 'toolname', ''));


		// Create a Tool object
		$obj = new Tool( $database );
		$objV = new ToolVersion( $database );
		
		if($id) {
			// get tool status before changes
			$obj->getToolStatus( $id, $this->_option, $oldstatus, $editversion, $ldap_read);
			
			// make sure user is authorized to go further
			if(!$this->check_access($id, $juser, $this->_admin) ) { 
				JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
				return; 
			}
					
		}

		// new tool or edit
		if($task=='register' or $task=='save') {

			if($objV->validToolReg($tool, $err, $id, $ldap_read, $this->config)) {

				// save tool info
				if(!$id) { // new tool
					$obj->published = 0;
					$obj->toolname = $toolname;
					$obj->state = 1;
					$obj->priority = 3;
					$obj->registered = $today;
					$obj->state_changed = $today;
					$obj->registered_by = $juser->get('username');
					$obj->title = $tool['title'];

					if (!$obj->store()) {
						$this->_error=$obj->getError();
						return;
					}
				}

				// get tool id for newly registered tool
				$this->_toolid = $id ? $id : $obj->getToolId($toolname);

				// save version info
				$binditems = array ('toolid'=>$this->_toolid, 'toolname'=>$toolname, 'title'=>$tool['title'], 'version'=>$tool['version'],
						'description'=>$tool['description'], 'toolaccess'=>$tool['exec'], 'codeaccess'=>$tool['code'], 'wikiaccess'=>$tool['wiki'], 'vnc_geometry'=>$tool['vncGeometry']);
				
				//*******exportControl
				$objV->exportControl = ContribtoolLdap::getldapExec($tool['exec']);

				if($editversion=='dev') {				
	
					$objV->state = 3; // this will indicate dev version
					$objV->instance = $toolname.$dev_siffix;
					$objV->title = $tool['title'];
					$objV->mw = isset($this->config->parameters['default_mw']) ? $this->config->parameters['default_mw'] : 'narwhal';
					//$objV->vnc_command = $invokedir.DS.$toolname.DS.'invoke';
					//$objV->vnc_command = $invokedir.DS.$toolname.DS.'dev'.DS.'middleware'.DS.'invoke';
					$objV->vnc_command = $invokedir.DS.$toolname.DS.'dev'.DS.'middleware'.DS.'invoke -T dev';
					
				}

				if (!$objV->bind($binditems)) {
					$this->_error=$objV->getError();
					return;
				}

				if (!$objV->save($this->_toolid, $editversion, 1) ) {
					$this->_error=$objV->getError();
					return;
				}
				
				if(!$this->_error) {
	
					// create/update developers group
					$objG = new ToolGroup( $database );
					$groupexists = $obj->getToolDevGroup($this->_toolid);
					$devgroup = $groupexists ? $groupexists : $group_prefix.$toolname;
					$objG->saveGroup($this->_toolid, $devgroup, $tool['developers'], $groupexists);
	
					// store/update member groups
					if(count($tool['membergroups'] > 0) && $tool['exec']=='@GROUP') {
						$objG->saveMemberGroups($this->_toolid, $tool['membergroups'], $editversion);
					}
	
					// get ticket information
					$ticketid = $obj->getTicketId($this->_toolid);
					$ticketid = $ticketid ? $ticketid : $this->createTicket($this->_toolid, $tool);
	
					// create resource page
					$rid = $obj->getResourceId($this->_toolid);
					$rid = $rid ? $rid : $this->createResPage($this->_toolid, $tool);
					
					// save authors
					$objA = new ToolAuthor( $database);
					if(!$id) { $objA->saveAuthors($tool['developers'], 'dev', $rid, '', $tool['toolname'] ); }
	
					// get tool status after updates
					$obj->getToolStatus( $this->_toolid, $this->_option, $status, $editversion, $ldap_read);
					
					// update ldap if applicable
					if($ldap_save) {
						if(!$id) { $status['vncCommand'] = $invokedir.DS.$toolname.DS.'dev'.DS.'middleware'.DS.'invoke -T dev';	 }					
						ContribtoolLdap::saveToLdap($toolname, $status, $id, $editversion, $devgroup, $this->_option);
					}
					
					// license new tool										
					if(!$id) {
						include_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_mw'.DS.'mw.license.php');
						$devid = $objV->getDevVersionProperty ($toolname, 'id');
						$objLic = new LicenseTool( $database);
						$lic = new License( $database);
						$license_id = $lic->getIdfromAlias('public');
						
						$objLic->license_id = $license_id ? $license_id : 10;
						$objLic->tool_id = $devid; // this is in fact version id!!
						$objLic->created = $today; 
						
						if ($objLic->check()) {
							$objLic->store();
						}
												
					}
	
					// update history ticket
					if($id && $oldstatus!=$status && $editversion !='current') { $this->updateTicket($this->_toolid, $oldstatus, $status, $comment); }
					
					// display status page
					$this->_task = 'status';
					$this->_msg = $id ? JText::_('NOTICE_TOOL_INFO_CHANGED'): JText::_('NOTICE_TOOL_INFO_REGISTERED');
					$this->status();
				}


			} //--------end if valid
			else {
				// display form with errors
				$title = JText::_(strtoupper($this->_name)).': '.JText::_('EDIT_TOOL');
				$document =& JFactory::getDocument();
				$document->setTitle( $title );

				if($this->_toolid) { $tool['published']=$oldstatus['published']; }

				echo ContribtoolHtml::writeToolForm($this->_option, $title, $this->_admin, $juser, $tool, $err, $id, $this->config, $this->_task);

			}

		} //---------end if register/save
		else {
			// update status/ priority/ admin actions & comments
			$newstate 		= JRequest::getVar( 'newstate', '');
			$priority 		= JRequest::getVar( 'priority', 3);
			$access 		= JRequest::getInt( 'access', 0);
			
			if($newstate && !intval($newstate)) { $newstate = ContribtoolHtml::getStatusNum($newstate); }
			
			$this->_toolid = $id ? $id : $obj->getToolId($toolname);
			$obj->load($this->_toolid); // load main tool info
			
			switch($task) 
			{	
				// finalize and publish new version of a tool
				case 'publishtool':
					
					if($this->_admin!=2) { // needs to be admin
						JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
						return; 
					}
					
					$this->publish($output);					
					echo '<p id="output" class="'.$output['class'].'">'.$output['msg'].'</p>';
					return;
				break;
				
				// run installtool script
				case 'installtool':
				
					if($this->_admin!=2) { // needs to be admin
						JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
						return; 
					}
					$this->installTool($output);
					echo '<p id="output" class="'.$output['class'].'">'.$output['msg'].'</p>';
					return;	
				break;
				
				// run addRepo script
				case 'createtool':
					if($this->_admin!=2) { // needs to be admin
						JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
						return; 
					}
					
					$this->addRepo($output);
					echo '<p id="output" class="'.$output['class'].'">'.$output['msg'].'</p>';
					return;						
				break;
				
				//retire tool
				case 'retiretool':
				
					if($this->_admin!=2) { // needs to be admin
						JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
						return; 
					}
					
					$this->retire ($output);
					echo '<p id="output" class="'.$output['class'].'">'.$output['msg'].'</p>';
					return;					
				break;
				
				// save version supplied by user
				case 'saveversion':
		
					$newversion 	= JRequest::getVar( 'newversion', '' );
					if($objV->validVersion($toolname, $newversion, $this->_error, $ldap_read, 1)) {
						$objV->version = $newversion; 
						
						// save version info
						if (!$objV->save($this->_toolid, $editversion) ) {
							$this->_error=$objV->getError();
							return;
						}	 
						else {
							if($this->_action == 'confirm') {
								$this->license (); 
								return; // display license page
							}
							else { 
								// get tool status after updates
								$obj->getToolStatus( $this->_toolid, $this->_option, $status, $editversion, $ldap_read);
		
								// update history ticket
								if($oldstatus!=$status) { $this->updateTicket($this->_toolid, $oldstatus, $status, ''); }
								$this->_msg = JText::_('NOTICE_CHANGE_VERSION_SAVED');
								$this->_task = 'status';
								$this->status(); return; 
							}	
						}	
																
					}
					else {
						$this->version (); // display version page with error
						return;
					}		
				break;
				
				// save version supplied by user
				case 'savelicense':
					
					$this->license_choice = array('text'=>strip_tags(JRequest::getVar( 'license', '')), 
					'template'=>JRequest::getVar( 'templates', 'c1'), 
					'authorize'=>JRequest::getInt( 'authorize', 0));
					$this->code = JRequest::getVar( 't_code', '@OPEN');
					
					if($objV->validLicense($toolname, $this->license_choice, $this->code, $this->_error)) {
							// code for saving license
							$objV->license = strip_tags($this->license_choice['text']);
							$objV->codeaccess = $this->code;
							
							// save version info
							if (!$objV->save($this->_toolid, $editversion) ) {
								$this->_error=$objV->getError();
								return;
							}	
							
							if($this->_action != 'confirm') {
								$this->_msg = JText::_('NOTICE_CHANGE_LICENSE_SAVED');
								$this->_task = 'status';
								$this->status(); 
								return; 												
							}
							else { 
								$this->finalize_version();
								return;
							}	
					}
					else {
						$this->license (); // display license page with error
						return;
					}	
								
				break;
				
				// all details confirmed, version approved
				case 'finalizeversion':

					$obj->state = $newstate;
					$obj->state_changed = $today;
						
					// save tool info
					if (!$obj->store()) {
						$this->_error=$obj->getError();
						return;
					}
					else {
						// get tool status after updates
						$obj->getToolStatus( $this->_toolid, $this->_option, $status, $editversion, $ldap_read);
	
						// update history ticket
						if($oldstatus!=$status) { $this->updateTicket($this->_toolid, $oldstatus, $status, '', 0, 1); }
						$this->_msg = JText::_('NOTICE_STATUS_CHANGED');
						$this->_task = 'status';
						$this->status(); 
						return;
					}						
				break;
				
				// updating status and/or priority
				case 'update':
	
					if(intval($newstate) && $newstate != $oldstatus['state']) {
						if($newstate == ContribtoolHtml::getStatusNum('Approved') && $objV->validVersion($toolname, $oldstatus['version'], $this->_error, $ldap_read, 1)) { // check for version
							$this->_action = 'confirm';
							$this->_task = JText::_('CONTRIBTOOL_APPROVE_TOOL');
							$this->version();
							return;
						}
						else if($newstate == ContribtoolHtml::getStatusNum('Approved')) {
							$this->_action = 'new';
							$this->_task = JText::_('CONTRIBTOOL_APPROVE_TOOL');
							$this->version();
							return;
						}
						else if($newstate == ContribtoolHtml::getStatusNum('Published')) {
							$objV->published = '1';		
						}
						$obj->state = $newstate;
						$obj->state_changed = $today;	
					}
					
					// if priority changes 
					if(intval($priority) && $priority != $oldstatus['priority']) {
						$obj->priority = $priority;			
					}
					
					// save tool info
					if (!$obj->store()) {
						$this->_error=$obj->getError();
						return;
					}
					else {
						// get tool status after updates
						$obj->getToolStatus( $this->_toolid, $this->_option, $status, $editversion, $ldap_read);
	
						// update history ticket
						if($oldstatus!=$status) { $this->updateTicket($this->_toolid, $oldstatus, $status, $comment, $access, 1); }
						$this->_msg = JText::_('NOTICE_STATUS_CHANGED');
						$this->_task = 'status';
						$this->status(); 
						return;
					}
					
				break;
				// sending a message
				case 'message':
					if($comment) {
						$this->updateTicket($this->_toolid, '', '', $comment, $access, 1);
						$this->_msg = JText::_('NOTICE_MSG_SENT');
					}
						$this->_task = 'status';
						$this->status(); 
				
				break;
					
			}			

		} //--------end if update

	}
	//-----------

	protected function email($toolid, $summary, $comment, $access)
	{
		$xhub 		=& XFactory::getHub();
		$juser     	=& JFactory::getUser();
		$database 	=& JFactory::getDBO();
		
		$obj = new Tool($database);
		$obj->getToolStatus( $toolid, $this->_option, $status, 'dev');
		$headline = $summary ? $summary : JText::_('UPDATE');
		if(!$summary && $comment) { $headline = JText::_('new message'); }
		
		// Build e-mail components
		$admin_email = isset($this->config->parameters['developer_email']) ? $this->config->parameters['developer_email'] : $xhub->getCfg('hubSupportEmail');
					
		$subject = $xhub->getCfg('hubShortName').' '.JText::_(strtoupper($this->_name)).', '.JText::_('TOOL').' '.$status['toolname'].'(#'.$toolid.'): '.$headline;
					
		$from = array();
		$from['name']  = $xhub->getCfg('hubShortName').' '.JText::_('APPS_DEVELOPMENT');
		$from['email'] = $xhub->getCfg('hubSupportEmail');
		
		$message  = '----------------------------'.r.n;
		$message .= strtoupper(JText::_('TOOL')).': '.$status['title'].' ('.$status['toolname'].')'.r.n;
		$message .= strtoupper(JText::_('SUMMARY')).': '.$summary.r.n;
		$message .= strtoupper(JText::_('WHEN')).' '.JHTML::_('date', date( 'Y-m-d H:i:s', time() ), '%d %b, %Y').r.n;
		$message .= strtoupper(JText::_('BY')).': '.$juser->get('username').r.n;
		$message .= '----------------------------'.r.n.r.n;
		if($comment) {
		$message .= strtoupper(JText::_('MESSAGE')).': '.r.n;
		$message .= $comment.r.n;
		$message .= '----------------------------'.r.n.r.n;
		}
		$message .= JText::_('TIP_URL_TO_STATUS').''.r.n;
		$message .= $xhub->getCfg('hubLongURL').JRoute::_('index.php?option=com_contribtool&task=status&toolid='.$toolid) .r.n;
		
		// An array for all the addresses to be e-mailed
		$emails = array();
		$teamemails = array();
		
		$team = ContribtoolHelper::transform($status['developers'], 'uidNumber');
		if(!$this->_admin) { $this->_admin = 0; }
		$inteam = (in_array($juser->get('uid'), $team)) ? 1 : 0;
		
		foreach ($team as $member) {
				$user =& XUser::getInstance ( $member );	
				$teamemails[] = $user->get('email');
		}
		
		// determine receipients
		if($this->_admin==2 && $access != 1) { 
			$emails = $teamemails; 
			if(!$inteam) {						
				$emails[] = $juser->get('email'); // cc admin who made the change if not in team
			}
			else { 
				$emails[] = $admin_email;
			}
		}
		else if ($access == 1) {					
			$emails[] = $admin_email; // send private message to admin
		}
		else if (!$this->_admin) { // send to admin & team - action by dev team or new registration					
			$emails = $teamemails;
			$emails[] = $admin_email;
		} 
		
		
		// fire off email
		if($summary or $comment) {
			foreach ($emails as $email)
			{
				SupportUtils::send_email($email, $subject, $message, $from);
			}
		}
				
	}

	//-----------

	protected function updateTicket($toolid, $oldstuff, $newstuff, $comment, $access=0, $email=0, $changelog=array())
	{
		$juser =& JFactory::getUser();
		$database =& JFactory::getDBO();

		$obj = new Tool( $database);
		$ticketid = $obj->getTicketId($toolid);
		$summary = '';

		// see what changed
		if($oldstuff != $newstuff) {
			if ($oldstuff['toolname'] != $newstuff['toolname']) {
				$changelog[] = '<li><strong>'.JText::_('TOOLNAME').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['toolname'].'</em> '.JText::_('TO').' <em>'.$newstuff['toolname'].'</em></li>';
			}
			if ($oldstuff['title'] != $newstuff['title']) {
				$changelog[] = '<li><strong>'.JText::_('TOOL').' '.strtolower(JText::_('TITLE')).'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['title'].'</em> '.JText::_('TO').' <em>'.$newstuff['title'].'</em></li>';
				$summary .= strtolower(JText::_('TITLE'));
			}
			if ($oldstuff['version']!='' && $oldstuff['version'] != $newstuff['version'] ) {
				$changelog[] = '<li><strong>'.strtolower(JText::_('DEV_VERSION_LABEL')).'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['version'].'</em> '.JText::_('TO').' <em>'.$newstuff['version'].'</em></li>';
				$summary .= ', '.strtolower(JText::_('VERSION'));
			}
			else if($oldstuff['version']=='' && $newstuff['version']!='') {
				$changelog[] = '<li><strong>'.strtolower(JText::_('DEV_VERSION_LABEL')).'</strong> '.JText::_('TICKET_SET_TO')
				.' <em>'.$newstuff['version'].'</em>';
			}
			if ($oldstuff['description'] != $newstuff['description']) {
				$changelog[] = '<li><strong>'.JText::_('TOOL').' '.strtolower(JText::_('DESCRIPTION')).'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['description'].'</em> '.JText::_('TO').' <em>'.$newstuff['description'].'</em></li>';
				$summary .= ', '.strtolower(JText::_('DESCRIPTION'));
			}
			if ($oldstuff['exec'] != $newstuff['exec']) {
				$changelog[] = '<li><strong>'.JText::_('TOOL_ACCESS').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['exec'].'</em> '.JText::_('TO').' <em>'.$newstuff['exec'].'</em></li>';
				if($newstuff['exec']=='@GROUP') {
				$changelog[] = '<li><strong>'.JText::_('ALLOWED_GROUPS').'</strong> '.JText::_('TICKET_SET_TO')
				.' to <em>'.ContribtoolHtml::getGroups($newstuff['membergroups']).'</em></li>';
				}
				$summary .= ', '.strtolower(JText::_('TOOL_ACCESS'));
			}
			if ($oldstuff['code'] != $newstuff['code']) {
				$changelog[] = '<li><strong>'.JText::_('CODE_ACCESS').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['code'].'</em> '.JText::_('TO').' <em>'.$newstuff['code'].'</em></li>';
				$summary .= ', '.strtolower(JText::_('CODE_ACCESS'));
			}
			if ($oldstuff['wiki'] != $newstuff['wiki']) {
				$changelog[] = '<li><strong>'.JText::_('WIKI_ACCESS').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['wiki'].'</em> '.JText::_('TO').' <em>'.$newstuff['wiki'].'</em></li>';
				$summary .= ', '.strtolower(JText::_('WIKI_ACCESS'));
			}
			if ($oldstuff['vncGeometry'] != $newstuff['vncGeometry']) {
				$changelog[] = '<li><strong>'.JText::_('VNC_GEOMETRY').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['vncGeometry'].'</em> to <em>'.$newstuff['vncGeometry'].'</em></li>';
				$summary .= ', '.strtolower(JText::_('VNC_GEOMETRY'));
			}
			if ($oldstuff['developers'] != $newstuff['developers']) {
				$changelog[] = '<li><strong>'.JText::_('DEVELOPMENT_TEAM').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.ContribtoolHtml::getDevTeam($oldstuff['developers']).'</em> '.JText::_('TO').' <em>'.ContribtoolHtml::getDevTeam($newstuff['developers']).'</em></li>';
				$summary .= ', '.strtolower(JText::_('DEVELOPMENT_TEAM'));
			}			
			if ($oldstuff['vncGeometry'] != $newstuff['vncGeometry']) {
				$changelog[] = '<li><strong>'.JText::_('VNC_GEOMETRY').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.$oldstuff['vncGeometry'].'</em> '.JText::_('TO').' <em>'.$newstuff['vncGeometry'].'</em></li>';
				$summary .= ', '.strtolower(JText::_('VNC_GEOMETRY'));
			}
			// end of tool information changes
			if($summary) {
				$summary .= ' '.JText::_('INFO_CHANGED');
			}
			
			// tool status/priority changes
			if ($oldstuff['priority'] != $newstuff['priority']) {
				$changelog[] = '<li><strong>'.JText::_('PRIORITY').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.ContribtoolHtml::getPriority($oldstuff['priority']).'</em> '.JText::_('TO').' <em>'.ContribtoolHtml::getPriority($newstuff['priority']).'</em></li>';
				$email = 0; // do not send email about priority changes
			}
			if ($oldstuff['state'] != $newstuff['state']) {
				$changelog[] = '<li><strong>'.JText::_('STATUS').'</strong> '.JText::_('TICKET_CHANGED_FROM')
				.' <em>'.ContribtoolHtml::getStatusName($oldstuff['state'], $oldstate).'</em> '.JText::_('TO').' <em>'.ContribtoolHtml::getStatusName($newstuff['state'], $newstate).'</em></li>';
				$summary = JText::_('STATUS').' '.JText::_('TICKET_CHANGED_FROM').' '.$oldstate.' '.JText::_('TO').' '.$newstate;
				$email = 1; // send email about status changes
			}
		}

		// Were there any changes?
		$log = implode(n,$changelog);
		if ($log != '') {
			$log = '<ul class="changelog">'.n.$log.'</ul>'.n;
		}

		$rowc = new SupportComment( $database );
		$rowc->ticket     = $ticketid;
		if($comment) {
		$rowc->comment    = nl2br($comment);
		$rowc->comment    = str_replace( '<br>', '<br />', $rowc->comment );
		}
		$rowc->created    = date( 'Y-m-d H:i:s', time() );
		$rowc->created_by = $juser->get('username');
		$rowc->changelog  = $log;
		$rowc->access     = $access;

		if (!$rowc->store()) {
			$this->_error = $rowc->getError();
			return false;
		}
		else if($email) { 
			// send notification emails
			$this->email($toolid, $summary, $comment, $access);
		}

		return true;

	}
	//-----------

	protected function createTicket($toolid, $tool)
	{
		$juser =& JFactory::getUser();
		$database =& JFactory::getDBO();

		// include support scripts
		include_once( JPATH_ROOT.DS.'components'.DS.'com_support'.DS.'support.tags.php' );
		include_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_support'.DS.'support.ticket.php' );
		include_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_support'.DS.'support.comment.php' );

		$st = new SupportTags( $database );
		$row = new SupportTicket( $database );
		$row->status = 0;
		$row->created =  date( "Y-m-d H:i:s" );
		$row->login = $juser->get('username');
		$row->severity = 'normal';
		$row->summary = JText::_('NEW_TOOL_SUBMISSION').': '.$tool['toolname'];
		$row->report = $tool['toolname'];
		$row->section = 2;
		$row->type = 3;
		$row->email = $juser->get('email');
		$row->name = $juser->get('name');

		if (!$row->store()) {
			$this->_error = $row->getError();
			return false;
		}
		else {
			// Checkin ticket
			$row->checkin();

			if($row->id) {
				// save tag
				$st->tag_object( $juser->get('id'), $row->id, 'tool:'.$tool['toolname'], 0, 0 );

				// store ticket id
				$obj = new Tool( $database);
				$obj->saveTicketId($toolid, $row->id);

				// make a record
				$this->updateTicket($toolid, '', '', JText::_('NOTICE_TOOL_REGISTERED'), $access=0, $email=1);
			}

		}

		return $row->id;
	}
	
	//-----------

	protected function updateResPage($rid, $status=array(), $published=0, $newtool=0)
	{
		
		$juser =& JFactory::getUser();
		$database =& JFactory::getDBO();
		
		if ($rid === NULL) {
			return false;
		}
		$resource = new ResourcesResource( $database );
		$resource->load( $rid);
		if(count($status) > 0) {
		$resource->fulltext = addslashes($status['fulltext']);
		$resource->introtext = $status['description'];
		$resource->title = $status['title'];
		$resource->modified = date( "Y-m-d H:i:s" );
		$resource->modified_by = $juser->get('id');
		}
		if($published) {
		$resource->published = $published;
		}
		if($newtool && $published==1) {
		$resource->publish_up = date( "Y-m-d H:i:s" );
		}
		if (!$resource->store()) {
			$this->_error = $row->getError();
			return false;
		}
		else if($newtool) {
			$this->_msg = JText::_('NOTICE_RES_PUBLISHED');
			return true;
		}
		else {
			$this->_msg = JText::_('NOTICE_RES_UPDATED');
			return true;
		}		
		
	}


	//-----------

	protected function createResPage($toolid, $tool)
	{

		$juser =& JFactory::getUser();
		$database =& JFactory::getDBO();

		$params = 'pageclass_sfx=
					show_title=1
					show_authors=1
					show_assocs=1
					show_type=1
					show_logicaltype=1
					show_rating=1
					show_date=1
					show_parents=1
					series_banner=
					show_banner=1
					show_footer=3
					show_stats=0
					st_appname='.strtolower($tool['toolname']).'
					st_appcaption='.$tool['title'].$tool['version'].'
					st_method=com_narwhal';

		// Initiate extended database class
		$row = new ResourcesResource( $database );
		$row->created_by = $juser->get('id');
		$row->created = date( 'Y-m-d H:i:s' );
		$row->published = '2';  // draft state
		$row->params = $params;
		$row->attribs = 'marknew=0';
		$row->standalone = '1';
		$row->type = '7';
		$binditems = array ('title'=>$tool['title'], 'introtext'=>$tool['description'],  'alias'=>strtolower($tool['toolname']) );

		if (!$row->bind($binditems)) {
			$this->_error = $row->getError();
			return false;
		}
		if (!$row->store()) {
			$this->_error = $row->getError();
			return false;
		}
		else {
			// Checkin resource
			$row->checkin();
		}

		return $row->id;
	}

	//-----------

	protected function cancel()
	{
		$database =& JFactory::getDBO();

	    $juser     =& JFactory::getUser();
		$xhub      =& XFactory::getHub();

		// get admin priviliges
		$this->authorize_admin();

		if (!$this->_toolid) {
			$this->_toolid = JRequest::getInt( 'toolid', 0 );
		}
		if (!$this->_error) {
			$this->_error = JRequest::getVar( 'error', '' );
		}
		
		// check access rights
		if($this->check_access($this->_toolid, $juser, $this->_admin) ) {

			// Create a Tool object
			$obj = new Tool( $database );

			// get tool status
			$obj->getToolStatus( $this->_toolid, $this->_option, $status, 'dev');

			if(!$status) {
				JError::raiseError( 404, JText::_('ERR_EDIT_CANNOT_FIND') );
				return;
			}
			if($status['state']== ContribtoolHtml::getStatusNum('Abandoned') ) {
				JError::raiseError( 404, JText::_('ERR_ALREADY_CANCELLED') );
				return;
			}
			if($status['published']== 1 ) {
				JError::raiseError( 404, JText::_('ERR_CANNOT_CANCEL_PUBLISHED_TOOL') );
				return;
			}
			
			// unpublish resource page
			$this->updateResPage($status['resourceid'], $status, '4');
			
			// change tool status to 'abandoned' and priority to 'lowest'
			$obj->updateTool($this->_toolid, ContribtoolHtml::getStatusNum('Abandoned') , 5);
					
			// close ticket
			$row = new SupportTicket( $database );
			$row->load($status['ticketid']);
			$row->status = 2;
			$row->created =  date( "Y-m-d H:i:s" );
			$row->store();
			
			// add comment to ticket
			$this->updateTicket($this->_toolid, '', '', JText::_('NOTICE_TOOL_CANCELLED'), $access=0, $email=1);					
			
		}
		else {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// continue output
		$this->_msg = JText::_('NOTICE_TOOL_CANCELLED');
		$this->status();
		
	}

	//----------------------------------------------------------
	// Run scripts
	//----------------------------------------------------------

	protected function licenseTool($toolname)
	{
		$token = md5(uniqid());
		$xhub   =& XFactory::getHub();
		$scriptdir = isset($this->config->parameters['adminscript_dir']) ? $this->config->parameters['adminscript_dir'] : DS.'apps'.DS.'bin';

		$fname = '/tmp/license'.$toolname.$token.'txt';
		$handle = fopen($fname, "w");
		
		fwrite($handle, $this->_output);
		fclose($handle);

		$command = '/bin/sh ' . $scriptdir.DS.'licensetool -hubdir '.JPATH_ROOT.' -type raw -license '.$fname.' '.$toolname;
		
		if(!$this->invokescript($command, JText::_('NOTICE_LICENSE_CHECKED_IN'), $output)) {
			return false;
		}
		else {
			unlink($fname);
			return true;
		}

	}

	//-----------

	protected function addRepo(&$output)
	{
		if(!$this->_toolid) {
			return false;
		}
				
		$xhub   =& XFactory::getHub();
		$database =& JFactory::getDBO();
		$pw 	= $xhub->getCfg('hubLDAPSearchUserPW');
		$scriptdir = isset($this->config->parameters['addrepo_dir']) ? $this->config->parameters['addrepo_dir'] : DS.'opt'.DS.'addrepo';
		$ldap = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		
		// Create a Tool object
		$obj = new Tool( $database );
		$obj->getToolStatus($this->_toolid, $this->_option, $status, 'dev', $ldap);
		
		if(count($status) > 0) {
			$command = $scriptdir.DS.'addrepo '.$status['toolname'].' -title "'.$status['title'].'" -description "'.$status['description'].'" -password "'.$pw.'"' . " -hubdir " . JPATH_ROOT;

			if(!$this->invokescript($command, JText::_('NOTICE_PROJECT_AREA_CREATED'), $output)) {
				return false;
			}
			else {
				return true;
			}
		}
		else {
			$output['class'] = 'error';
			$output['msg'] = JText::_('ERR_CANNOT_RETRIEVE');
			return false;
		}
	}

	//-----------

	protected function installTool(&$output)
	{
		if(!$this->_toolid) {
			return false;
		}
				
		$database =& JFactory::getDBO();
		$xhub   =& XFactory::getHub();
		$ldap = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		$scriptdir = isset($this->config->parameters['adminscript_dir']) ? $this->config->parameters['adminscript_dir'] : DS.'apps'.DS.'bin';
		
		// Create a Tool object
		$obj = new Tool( $database );
		$obj->getToolStatus($this->_toolid, $this->_option, $status, 'dev', $ldap);
		if(count($status) > 0) {
			$command = '/bin/bash ' . $scriptdir.DS.'installtool -type raw -hubdir '.JPATH_ROOT.' '.$status['toolname'];

			if(!$this->invokescript($command, JText::_('NOTICE_REV_INSTALLED'), $output)) {
				return false;
			}
			else {
				 // extract revision number
				$rev = explode("installed revision: ", $output['msg']);
				if(isset($rev[1]) && intval($rev[1])) {
					$objV = new ToolVersion( $database );
					$objV->revision = intval($rev[1]);
					if (!$objV->save($this->_toolid, 'dev')) {
						$output['class'] = 'error';
						$output['msg'] .= '<br />* '.$objV->getError();
						return false;
					}
					else {
						return true;
					}
				}
				else {
					$output['class'] = 'error';
					$output['msg'] .= '<br />* '.JText::_('ERR_CANNOT_SAVE_REVISION_INFO');
				}
			}
		}
		else {
		$output['class'] = 'error';
		$output['msg'] = JText::_('ERR_CANNOT_RETRIEVE');
		return false;
		}

	}

	//-----------

	protected function finalizeTool(&$out)
	{
		if(!$this->_toolid) {
			return false;
		}
		
		$database =& JFactory::getDBO();
		$xhub   =& XFactory::getHub();
		$ldap = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		$scriptdir = isset($this->config->parameters['adminscript_dir']) ? $this->config->parameters['adminscript_dir'] : DS.'apps'.DS.'bin';
		//$tarball_path = $this->rconfig->get('uploadpath');
		$tarball_path = $xhub->getCfg('sourcecodePath');
		
		// Create a Tool object
		$obj = new Tool( $database );
		$obj->getToolStatus($this->_toolid, $this->_option, $status, 'dev', $ldap);
		if(count($status) > 0) {
			
			// Make sure the path exist
			if (!is_dir( '/tmp' )) {
				jimport('joomla.filesystem.folder');
				if (!JFolder::create( '/tmp', 0777 )) {
					$out .= JText::_('ERR_UNABLE_TO_CREATE_PATH').' /tmp';
					return false;
				}
			}
		
		    $fname = DS.'tmp'.DS.'license'.$this->_toolid.'-r'.$status['revision'].'txt';
			$handle = fopen($fname, "w");
			fwrite($handle, $status['license']);
			fclose($handle);

			$command = '/bin/sh ' . $scriptdir.DS.'finalizetool -hubdir '.JPATH_ROOT.' -title "'.$status['title'].'" -version "'.$status['version'].'" -license '.$fname.' '.$status['toolname'];

			if(!$this->invokescript($command, JText::_('NOTICE_VERSION_FINALIZED'), $output)) {
				return false;
			}
			else {
				
			 	// get tarball
				$tar = explode("source tarball: /tmp/", $output['msg']);
				$tar = $tar[1];
	
				$file_path = $tarball_path.DS.$status['toolname'];
				
				// Make sure the upload path exist
				if (!is_dir( $file_path )) {
					jimport('joomla.filesystem.folder');
					if (!JFolder::create( $file_path, 0777 )) {
						$out .= JText::_('ERR_UNABLE_TO_CREATE_TAR_PATH');
						return false;
					}
				}
				if (!copy(DS.'tmp'.DS.$tar, $file_path.'/'.$tar)) {
    				$out.= " failed to copy $tar to $file_path";
					return false;
				} else {
					exec ('sudo -u apps rm -f /tmp/'.$tar, $out, $result);
				}

				return true;

			}
			unlink($fname);

		}
		else {
			$out = JText::_('ERR_CANNOT_RETRIEVE');
			return false;
		}

		return true;

	}

	//-----------

	protected function invokescript( $command, $successmsg, &$output, $success = 1)
	{
		$output['class'] 	= 'passed';
		$output['msg']		= '';

		exec($command.' 2>&1 </dev/null', $rawoutput, $status);

		if ($status != 0) {
			$output['class'] = 'error' ;
			$output['msg'] = JText::_('ERR_OPERATION_FAILED');
			$success = 0;
		}

		if($success) {
			$output['msg'] = JText::_('SUCCESS').': '.$successmsg;
		}
		
			$msg = '';
			// Print out results or errors
			foreach($rawoutput as $line)
			{
				$msg .= '<br /> * '.$line;
				$output['msg'] .= $msg;
			}
		
		return true;
	}

	//-----------

	protected function retire (&$output, $result = 1)
	{
		$database 	=& JFactory::getDBO();
		$ldap = isset($this->config->parameters['ldap_save']) ? $this->config->parameters['ldap_save'] : 0;

		$output = array('class'=>'passed', 'msg'=>JText::_('NOTICE_SUCCESS_TOOL_RETIRED'), 'pass'=>'', 'fail'=>'');

		// get current status
		$obj = new Tool( $database );
		$obj->getToolStatus($this->_toolid, $this->_option, $status, 'dev', $ldap);
		
		if(count($status) <=0) {
			$result = 0;
			$output['fail'] .= JText::_('ERR_STATUS_CANNOT_FIND');
		}
		else {
			// create a Tool Version object
			$objV = new ToolVersion( $database );
					
			// unpublish all previous versions
			if(!$objV->unpublish($this->_toolid)) {
				$result = 0;
				$output['fail'] .= '<br />* '.JText::_('ERR_FAILED_TO_UNPUBLISH_PREV_VERSIONS');
			}
			else {
				$output['pass'] .= '<br />* '.JText::_('NOTICE_UNPUBLISHED_PREV_VERSIONS');
			}

			if($ldap) { 
				if(ContribtoolLdap::unpublishVersion($tatus['toolname'], 'all')) {
					$output['pass'] .= '<br />* '.JText::_('NOTICE_UNPUBLISHED_PREV_VERSIONS_LDAP');
				}
				else {
					$output['fail'] .= '<br />* '.JText::_('ERR_FAILED_TO_UNPUBLISH_PREV_VERSIONS_LDAP');
				}
	
			}
		}
		
		// format output
		if(!$result) { 
			$output['class'] = 'error';
			$output['msg'] = JText::_('ERR_OPERATION_FAILED');
			$output['msg'] .= $output['fail'];
			$output['msg'] .= $output['pass'] ? '<br />'.JText::_('NOTICE_OK_ACTIONS').$output['pass'] : '';
		}
		else {
			$output['msg'] .= $output['pass'];
			$output['msg'] .= $output['fail'] ? '<br />'.JText::_('NOTICE_PROBLEMS').$output['fail'] : '';
		}
		
		return $result;

	}

	//-----------

	protected function publish(&$output, $result = 1)
	{

		$database 		=& JFactory::getDBO();
		$now 			= date( 'Y-m-d H:i:s' );
		$xhub 			=& XFactory::getHub();
		$hubShortName 	= $xhub->getCfg('hubShortName');
		$app 			=& JFactory::getApplication();
		$livesite 		= $xhub->getCfg('hubLongURL');
		$juser =& JFactory::getUser();
		
		$doiprefix 		= isset($this->config->parameters['doi_prefix']) ? $this->config->parameters['doi_prefix'] : '';
			
		// get config
		$ldap_save = isset($this->config->parameters['ldap_save']) ? $this->config->parameters['ldap_save'] : 0;
		$ldap_read = isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		$doiservice = isset($this->config->parameters['doi_service']) ? $this->config->parameters['doi_service'] : 'http://dir1.lib.purdue.edu:8080/axis/services/CreateHandleService?wsdl';
		$usedoi = isset($this->config->parameters['usedoi']) ? $this->config->parameters['usedoi'] : 0;
		$doiprefix = $doiprefix ? $doiprefix : strtolower($hubShortName).'-r';
		$invokedir = isset($this->config->parameters['invokescript_dir']) ? $this->config->parameters['invokescript_dir'] : DS.'apps';

		$output = array('class'=>'passed', 'msg'=>JText::_('NOTICE_SUCCESS_TOOL_PUBLISHED'), 'pass'=>'', 'fail'=>'');
		
		// get current status
		$obj = new Tool( $database );
		$obj->getToolStatus($this->_toolid, $this->_option, $status, 'dev', $ldap_read);
		
		if(count($status) <=0) {
			$result = 0;
			$output['fail'] .= JText::_('ERR_STATUS_CANNOT_FIND');
		}
		else {
			
			// Create a Tool Version object
			$objV = new ToolVersion( $database );
			$objV->getToolVersions( $this->_toolid, $tools, '', $ldap_read, 1);
					
			// make checks
			if(!is_numeric($status['revision'])) {  // bad format
				$result = 0;
				$output['fail'] .= '<br />* '.JText::_('ERR_MISSING_REVISION_OR_BAD_FORMAT');
			}

			else if(count($tools) > 0 && $status['revision']) {
				// check for duplicate revision
				foreach ($tools as $t) {
					if($t->revision == $status['revision']) {
						$result = 0;
						$output['fail'] .= '<br />* '.JText::_('ERR_REVISION_EXISTS').' '.$status['revision'];
					}
				}
				// check that revision number is greater than in previous version
				$currentrev = $objV->getCurrentVersionProperty ($status['toolname'], 'revision');			
				if($currentrev && (intval($currentrev) > intval($status['revision']))) {
					$result = 0;
					$output['fail'] .= '<br />* '.JText::_('ERR_REVISION_GREATER');
				}
			}
			
			// check if version is valid
			$objV->validVersion($status['toolname'], $status['version'], $error_v, $ldap_read, 1);
			if($error_v) { $result = 0; $output['fail'] .= '<br />* '.$error_v; }

		}
		
		// run finalizetool
	
		if($result) {
			if($this->finalizeTool($out='')) {
				$output['pass'] .= '<br />* Version finalized. '.$out;
			}
			else {
				$output['fail'] .= ($out) ? '<br />* '.$out : '';
				$result = 0;
			}
		}
		

		// register DOI handle

		if($result && $usedoi) {
					
			$url = $livesite.'/resources/'.$status['resourceid'].'/?rev='.$status['revision'];
			
			$objDOI = new ResourcesDoi ($database);	
			$bingo = $objDOI->getDoi($status['resourceid'], $status['revision']);
	
			if($bingo) { // handle already exists for this revision
				$output['fail'] .= '<br />* '.JText::_('ERR_DOI_ALREADY_EXISTS');
			}
			else {
				$latestdoi = $objDOI->getLatestDoi($status['resourceid']);
				$newlabel = ($latestdoi) ? (intval($latestdoi) + 1): 1;
				$handle = $doiprefix.$status['resourceid'].'.'.$newlabel;

				if($objDOI->createDOIHandle($url, $handle, $doiservice, $err)) {

					if($objDOI->saveDOI($status['revision'], $newlabel, $status['resourceid'],$status['toolname'])) {
						$output['pass'] .= '<br />* '.JText::_('SUCCESS_DOI_CREATED').' '.$handle;
					}
					else {
						$output['fail'] .= '<br />* '.JText::_('ERR_DOI_STORE_FAILED');
						$result = 0;
					}
				}
				else {
				print_r($err);
					if(ereg('HANDLE ALREADY EXISTS',$err) && !$bingo) {
						$output['fail'] .= '<br />* '.JText::_('ERR_DOI_ALREADY_EXISTS_COMPLAIN');
						
						if($objDOI->saveDOI($status['revision'], $newlabel, $status['resourceid'],$status['toolname'])) {
							$output['pass'] .= '<br />* '.JText::_('SUCCESS_DOI_FIXED').' '.$handle;
						}
						else {
							$output['fail'] .= '<br />* '.JText::_('ERR_DOI_STORE_FAILED');
							$result = 0;
						}
						
					} else {
						$output['fail'] .= '<br />* '.JText::_('ERR_DOI_FAILED');
						$result = 0;
					}
					$output['fail'] .= '<br />* '.JText::_('URL').': '.$url;
					$output['fail'] .= '<br />* '.JText::_('HANDLE').': '.$handle;
					$output['fail'] .= '<br />* '.$err;
				}
			}

		}


		// ldap actions
	
		if($result && $ldap_save) {
			
			// create/update developers group
			$objG = new ToolGroup( $database );
			$devgroup = $obj->getToolDevGroup($this->_toolid);
					
			// unpublish previous published version
			if(ContribtoolLdap::unpublishVersion($status['toolname'], 'previous')) {
				$output['pass'] .= '<br />* '.JText::_('NOTICE_UNPUBLISHED_PREV_VERSION_LDAP').' '.$handle;
			}
			else {
				$output['fail'] .= '<br />* '.JText::_('ERR_FAILED_TO_UNPUBLISH_PREV_VERSION_LDAP');
			}
			
			$status['vncCommand'] = $invokedir.DS.$status['toolname'].DS.'r'.$status['revision'].DS.'middleware'.DS.'invoke -T r'.$status['revision'];
			
			// save new version in LDAP
			if(ContribtoolLdap::saveToLdap($status['toolname'], $status, $this->_toolid, 'new', $devgroup, $this->_option)) {
				$output['pass'] .= '<br />* '.JText::_('SUCCESS_LDAP_RECORD_CREATED').' '.$handle;
			} else {
				$output['fail'] .= '<br />* '.JText::_('ERR_LDAP_SAVE_FAILED');
				$result = 0;
			}
		}
		
		
		
		// create tool instance in the database
		if($result) {
		
			// new licensing stuff
			include_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_mw'.DS.'mw.license.php');
			$objLic = new LicenseTool( $database);
			$lic = new License( $database);
			$license_id = $lic->getIdfromAlias('public');
			$license_id = $license_id ? $license_id : 10; 
		
		
			$newtool = $status['toolname'].'_r'.$status['revision'];
			
			// get version id
			$currentid = $objV->getCurrentVersionProperty ($status['toolname'], 'id');
			$new = ($currentid) ? 0 : 1;
			$devid = $objV->getDevVersionProperty ($status['toolname'], 'id');
			
			// Get the right invoke path
			
			//$invoke = $invokedir.DS.$status['toolname'].DS.'invoke';	
			$invoke = $invokedir.DS.$status['toolname'].DS.'r'.$status['revision'].DS.'middleware'.DS.'invoke -T r'.$status['revision'];	
						
			// create new version
			$binditems = array ('id'=>0, 'toolname'=>$status['toolname'], 'instance'=>$newtool, 'toolid'=>$this->_toolid, 'state'=>1, 'title'=>$status['title'], 
				'version'=>$status['version'], 'revision'=>$status['revision'], 'description'=>$status['description'], 'toolaccess'=>$status['exec'], 'codeaccess'=>$status['code'], 
				'wikiaccess'=>$status['wiki'], 'vnc_geometry'=>$status['vncGeometry'], 'vnc_command'=>$invoke, 'mw'=>$status['mw'], 
				'released'=>$now, 'released_by'=>$juser->get('username'), 'license'=>$status['license'], 'fulltext'=>$status['fulltext']);
			
			//*******exportControl
			$objV->exportControl = ContribtoolLdap::getldapExec($status['exec']);
	
			if (!$objV->bind($binditems)) {
				$output['fail'] .= '<br />* '.$objV->getError();
				$result = 0;
			}
			if (!$objV->store()) {
				$output['fail'] .= '<br />* '.$objV->getError();
				$result = 0;
			}
			else {
				// update tool entry
				$obj->load($this->_toolid);
				if($obj->published!=1) {
					$obj->published = 1;
					// save tool info
					if (!$obj->store()) {
						$output['fail'] .= '<br />* '.$obj->getError();
					}
					else {
						$output['pass'] .= '<br />* '.JText::_('NOTICE_TOOL_MARKED_PUBLISHED');
					}
				}
				
				// unpublish previous version
				if(!$new) {
					if($objV->unpublish($this->_toolid, $currentid)) {
						$output['pass'] .= '<br />* '.JText::_('NOTICE_UNPUBLISHED_PREV_VERSION_DB');
						
						// remove public license
						if($objLic->delete($license_id, $currentid)) {
						$output['pass'] .= '<br />* '.JText::_('Previous license removed in the database.');
						}
						else {
						$output['fail'] .= '<br />* '.JText::_('Failed to remove previous license from the database');
						}
						
					}
					else {
						$output['fail'] .= '<br />* '.JText::_('ERR_FAILED_TO_UNPUBLISH_PREV_VERSION_DB');
					}
				}
				
				// get version id
				$currentid = $objV->getCurrentVersionProperty ($status['toolname'], 'id');
				
				// save authors for this version
				$objA = new ToolAuthor( $database);
				$objA->saveAuthors($status['developers'], $currentid, $status['resourceid'], $status['revision'], $status['toolname'] );
				
				// transfer screenshots				
				if($this->transferScreenshots($devid, $currentid, $status['resourceid'])) {
					$output['pass'] .= '<br />* '.JText::_('Screenshots (if avaliable) transferred successfully.');
				}
				else {
					$output['fail'] .= '<br />* '.JText::_('There was a problem transferring screenshots.');
				}
				
				// license new version																	
				$objLic->license_id = $license_id ? $license_id : 10;
				$objLic->tool_id = $currentid; // this is in fact version id!!
				$objLic->created = $now; 
						
				if ($objLic->check()) {
					if($objLic->store() ) {
						$output['pass'] .= '<br />* '.JText::_('License created in the database');
					}
					else {
						$output['fail'] .= '<br />* '.JText::_('Failed to create license in the database.');
					}
				}
					
				
				// update and publish resource page
				$this->updateResPage($status['resourceid'], $status, '1', $new);
				
			}
					
		}
	
		// format output
		if(!$result) { 
			$output['class'] = 'error';
			$output['msg'] = JText::_('ERR_OPERATION_FAILED');
			$output['msg'] .= $output['fail'];
			$output['msg'] .= $output['pass'] ? '<br />'.JText::_('NOTICE_OK_ACTIONS').$output['pass'] : '';
		}
		else {
			$output['msg'] .= $output['pass'];
			$output['msg'] .= $output['fail'] ? '<br />'.JText::_('NOTICE_PROBLEMS').$output['fail'] : '';
		}
		
		return $result;

	}

	//----------------------------------------------------------
	// Resource page editing
	//----------------------------------------------------------

	protected function edit_resource()
	{
		$database 	=& JFactory::getDBO();
		$juser  	=& JFactory::getUser();
		$xhub      	=& XFactory::getHub();
		$ldap 		= isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		
		// get admin priviliges
		$this->authorize_admin();
		
		$rid 		= JRequest::getInt( 'rid', 0);
		$version 	= JRequest::getVar( 'editversion', 'dev');
		//$version 	= 'dev'; // default to dev version
		$step		= JRequest::getInt( 'step', 1);
		
		$obj = new Tool($database);
		$this->_toolid = $obj->getToolIdFromResource($rid);
		
		if(!$this->_toolid) {
			// not a tool resource page
			JError::raiseError( 404, JText::_('RESOURCE_NOT_FOUND') );
			return;
		}
		
		// make sure user is authorized to go further
		if(!$this->check_access($this->_toolid, $juser, $this->_admin) ) { 
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return; 
		}

		$nextstep = $step + 1;
	
		// get tool version (dev or current) information
		$obj->getToolStatus($this->_toolid, $this->_option, $status, $version, $ldap);
	

		// get resource information
		$row = new ResourcesResource( $database );
		$row->load( $rid );
		if(!$status['fulltext'])  { $status['fulltext'] = $row->fulltext; }

		
		// process first step
		if($nextstep==3 && isset($_POST['nbtag'])) {
				
			$objV = new ToolVersion ($database);
			if (!$objV->bind( $_POST )) {
					$this->_error=$objV->getError();
					return;
			}

			$body = stripslashes($_POST['fulltext']);
			if (preg_match("/([\<])([^\>]{1,})*([\>])/i", $body )) {
				// Do nothing
				$status['fulltext'] = trim(stripslashes($body));
			} else {
				// Wiki format will be used
				$status['fulltext'] = JRequest::getVar( 'fulltext', $status['fulltext'], 'post');
				
			}
			
			// Get custom areas, add wrapper tags, and compile into fulltext
			$nbtag = $_POST['nbtag'];
			$nbtag = array_map('trim',$nbtag);
			foreach ($nbtag as $tagname=>$tagcontent)
			{
				if ($tagcontent != '') {
					$status['fulltext'] .= '<nb:'.$tagname.'>'.$tagcontent.'</nb:'.$tagname.'>';
				}
			}
		
						
			$objV->fulltext   = $status['fulltext'];
			$objV->description  = $this->txt_shorten(JRequest::getVar( 'description', $status['description'], 'post'));
			$objV->title  = $this->txt_shorten(JRequest::getVar( 'title', $status['title'], 'post'));

			if (!$objV->save($this->_toolid, $version) ) {
				$this->_error=$objV->getError();
				return;
			} else {
				// get updated tool status
				$obj->getToolStatus($this->_toolid, $this->_option, $status, $version, $ldap);
					
			}
			
			if($version=='dev') {
				// update resource page
				$this->updateResPage($rid, $status);
			}
			
		}
		
	
		// Group access
		$accesses = array('Public','Registered','Special','Protected','Private');		
		$lists = array();
		$lists['access'] = ContribtoolHtml::selectAccess($accesses, $row->access);
		ximport('xuserhelper');			
		$groups = XUserHelper::getGroups( $juser->get('id'), 'members' );
		
		// Tags
		$nbtags = explode(',',$this->rconfig->get('tagstool'));
		foreach ($nbtags as $nbtag)
		{
			$nbtag = strtolower(trim($nbtag));
			$nbtag = str_replace(' ','', $nbtag);
			// explore the text and pull out all matches
			$allnbtags[$nbtag] = ContribtoolHtml::parseTag($status['fulltext'], $nbtag);
			// clean the original text of any matches
			$status['fulltext']  = str_replace('<nb:'.$nbtag.'>'.$allnbtags[$nbtag].'</nb:'.$nbtag.'>','',$status['fulltext']);
		}
		$status['fulltext'] = trim(stripslashes($status['fulltext']));
		$status['fulltext'] = preg_replace('/<br\\s*?\/??>/i', "", $status['fulltext']);
		$status['fulltext'] = ContribtoolHtml::txt_unpee($status['fulltext']);
			
		// get authors
		$objA = new ToolAuthor( $database);	
		$authors = ($version=='current') ? $objA->getToolAuthors($version, $rid, $status['toolname']) : array();
		//$authors= $objA->get_author_info ( $authors);
		
		// --------------------tags
		$tags  = JRequest::getVar( 'tags', '', 'post' );
		$tagfa = JRequest::getVar( 'tagfa', '', 'post' );
			
		// Get any HUB focus areas
		// These are used where any resource is required to have one of these tags
		$xhub =& XFactory::getHub();
		$fa1 = $xhub->getCfg('hubFocusArea1');
		$fa2 = $xhub->getCfg('hubFocusArea2');
		$fa3 = $xhub->getCfg('hubFocusArea3');
		$fa4 = $xhub->getCfg('hubFocusArea4');
		$fa5 = $xhub->getCfg('hubFocusArea5');
		$fa6 = $xhub->getCfg('hubFocusArea6');
		$fa7 = $xhub->getCfg('hubFocusArea7');
		$fa8 = $xhub->getCfg('hubFocusArea8');
		$fa9 = $xhub->getCfg('hubFocusArea9');
		$fa10 = $xhub->getCfg('hubFocusArea10');

		
		// Instantiate our tag object
		$tagcloud = new ResourcesTags($database);

		// Normalize the focus areas
		$tagfa1 = $tagcloud->normalize_tag($fa1);
		$tagfa2 = $tagcloud->normalize_tag($fa2);
		$tagfa3 = $tagcloud->normalize_tag($fa3);
		$tagfa4 = $tagcloud->normalize_tag($fa4);
		$tagfa5 = $tagcloud->normalize_tag($fa5);
		$tagfa6 = $tagcloud->normalize_tag($fa6);
		$tagfa7 = $tagcloud->normalize_tag($fa7);
		$tagfa8 = $tagcloud->normalize_tag($fa8);
		$tagfa9 = $tagcloud->normalize_tag($fa9);
		$tagfa10 = $tagcloud->normalize_tag($fa10);
		
		// process new tags
		if($tags or $tagfa) {
			$newtags = '';
			if($tagfa) { $newtags = $tagfa.', '; }
			if($tags) { $newtags .= $tags;  }
			$tagcloud->tag_object($juser->get('id'), $rid, $newtags, 1, 0);
		}
		
		// Get all the tags on this resource
		$tags_men = $tagcloud->get_tags_on_object($rid, 0, 0, 0, 0);
		$mytagarray = array();
		/*$fas = array($tagfa1,$tagfa2,$tagfa3,$tagfa4);
		$fats[$fa1] = $tagfa1;
		$fats[$fa2] = $tagfa2;
		$fats[$fa3] = $tagfa3;
		$fats[$fa4] = $tagfa4;*/
		$fas = array($tagfa1,$tagfa2,$tagfa3,$tagfa4,$tagfa5,$tagfa6,$tagfa7,$tagfa8,$tagfa9,$tagfa10);
		$fats = array();
		if ($fa1) {
			$fats[$fa1] = $tagfa1;
		}
		if ($fa2) {
			$fats[$fa2] = $tagfa2;
		}
		if ($fa3) {
			$fats[$fa3] = $tagfa3;
		}
		if ($fa4) {
			$fats[$fa4] = $tagfa4;
		}
		if ($fa5) {
			$fats[$fa5] = $tagfa5;
		}
		if ($fa6) {
			$fats[$fa6] = $tagfa6;
		}
		if ($fa7) {
			$fats[$fa7] = $tagfa7;
		}
		if ($fa8) {
			$fats[$fa8] = $tagfa8;
		}
		if ($fa9) {
			$fats[$fa9] = $tagfa9;
		}
		if ($fa10) {
			$fats[$fa10] = $tagfa10;
		}
			
		// Loop through all the tags and pull out the focus areas - those will be displayed differently
		foreach ($tags_men as $tag_men)
		{
			if (in_array($tag_men['tag'],$fas)) {
				$tagfa = $tag_men['tag'];
			} else {
				$mytagarray[] = $tag_men['raw_tag'];
			}
		}
		$tags = implode( ', ', $mytagarray );
			
		// add the CSS to the template 
		$document = &JFactory::getDocument();
		$document->addScript("components/com_contribute/contribute.js");
		$this->getStyles();	
		$this->getScripts();
		
		$title = JText::_(strtoupper($this->_name)).': '.JText::_('EDIT_TOOL_PAGE');
		$title .= ' ('.$status['toolname'].')';
		$document =& JFactory::getDocument();
		$document->setTitle( $title );	
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_('STATUS').' '.JText::_('FOR').' '.$status['toolname'], 'index.php?option='.$this->_option.a.'task=status'.a.'toolid='.$this->_toolid );
			$pathway->addItem( JText::_('EDIT_TOOL_PAGE'), 'index.php?option='.$this->_option.a.'task=start'.a.'step=1'.a.'rid='.$rid );

		}	

		echo ContribtoolHtml::writeResourceEditForm ($rid, $this->_toolid, $status, $row, $version, $allnbtags, $step, $this->_option, $this->_admin, $tags, $tagfa, $fats, $authors, $title, $groups);

	}

	//-----------

	protected function preview_resource ( )
	{
	    $database 	=& JFactory::getDBO();
		$juser  	=& JFactory::getUser();
		$xhub      	=& XFactory::getHub();
		$ldap 		= isset($this->config->parameters['ldap_read']) ? $this->config->parameters['ldap_read'] : 0;
		
		// get admin priviliges
		$this->authorize_admin();
		
		$rid 		= JRequest::getInt( 'rid', 0);
		$version 	= JRequest::getVar( 'editversion', 'dev');
		//$version 	= 'dev'; // default to dev version
		
		$obj = new Tool($database);
		$this->_toolid = $obj->getToolIdFromResource($rid);
		
		if(!$this->_toolid) {
			// not a tool resource page
			JError::raiseError( 404, JText::_('RESOURCE_NOT_FOUND') );
			return;
		}
		
		// make sure user is authorized to go further
		if(!$this->check_access($this->_toolid, $juser, $this->_admin) ) { 
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return; 
		}

		// Instantiate our tag object
		$tagcloud = new ResourcesTags($database);
		$tags  = JRequest::getVar( 'tags', '', 'post' );
		$tagfa = JRequest::getVar( 'tagfa', '', 'post' );
		// process new tags
		if($tags or $tagfa) {
			$newtags = '';
			if($tagfa) { $newtags = $tagfa.', '; }
			if($tags) { $newtags .= $tags;  }
			$tagcloud->tag_object($juser->get('id'), $rid, $newtags, 1, 0);
		}
		

		// Get some needed libraries
		include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
		
		$resource = new ResourcesResource( $database );
		$resource->load( $rid );
		
		$juser =& JFactory::getUser();
		if (!$juser->get('guest')) {
			ximport('xuserhelper');
			$xgroups = XUserHelper::getGroups($juser->get('id'), 'all');
			// Get the groups the user has access to
			$usersgroups = $this->_getUsersGroups($xgroups);
		} else {
			$usersgroups = array();
		}
		
		// get updated version
		$objV = new ToolVersion($database);
		
		$thistool = $objV->getVersionInfo('', $version, $resource->alias, '', $ldap);
		$thistool = $thistool ? $thistool[0] : '';
		
		// replace resource info with requested version
		$objV->compileResource ($thistool, '', &$resource, 'dev', $this->rconfig);
		
		// get language library
		$lang =& JFactory::getLanguage();
		if (!$lang->load( strtolower('com_resources'), JPATH_BASE)) {
			$this->setError( JText::_('Failed to load language file') );
		}

		// add the CSS to the template 
		$document = &JFactory::getDocument();
		$document->addScript("components/com_contribute/contribute.js");
		$this->getStyles();	
		$this->getStyles('com_resources');
		$this->getScripts();
		
		$title = JText::_(strtoupper($this->_name)).': '.JText::_('PREVIEW_TOOL_PAGE');
		$title .= ' ('.$resource->alias.')';
		$document =& JFactory::getDocument();
		$document->setTitle( $title );
		
		$app =& JFactory::getApplication();
		$pathway =& $app->getPathway();
		if (count($pathway->getPathWay()) <= 0) {
			$pathway->addItem( JText::_(strtoupper($this->_name)), 'index.php?option='.$this->_option );
		}
		if (count($pathway->getPathWay()) <= 1) {
			$pathway->addItem( JText::_('STATUS').' '.JText::_('FOR').' '.$thistool->toolname, 'index.php?option='.$this->_option.a.'task=status'.a.'toolid='.$this->_toolid );
			$pathway->addItem( JText::_('EDIT_TOOL_PAGE'), 'index.php?option='.$this->_option.a.'task=start'.a.'step=1'.a.'rid='.$rid );

		}			

		echo ContribtoolHtml::writeResourcePreview ( $database, $this->_option, 'status', $rid, $this->_toolid, $resource,  $this->rconfig, $usersgroups, $version, $title);
	
	}

	//----------------------------------------------------------
	// Misc resource editing
	//----------------------------------------------------------

	protected function txt_shorten($text, $chars=500)
	{
		$text = strip_tags($text);
		$text = trim($text);

		if (strlen($text) > $chars) {
			$text = $text.' ';
			$text = substr($text,0,$chars);
			$text = substr($text,0,strrpos($text,' '));
			$text = $text.' ...';
		}

		return $text;
	}

	//-----------

	protected function txt_autop($pee, $br = 1)
	{
		// converts paragraphs of text into xhtml
		$pee = $pee . "\n"; // just to make things a little easier, pad the end
		$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
		$pee = preg_replace('!(<(?:table|ul|ol|li|pre|form|blockquote|h[1-6])[^>]*>)!', "\n$1", $pee); // Space things out a little
		$pee = preg_replace('!(</(?:table|ul|ol|li|pre|form|blockquote|h[1-6])>)!', "$1\n", $pee); // Space things out a little
		$pee = preg_replace("/(\r\n|\r)/", "\n", $pee); // cross-platform newlines
		$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
		$pee = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "\t<p>$1</p>\n", $pee); // make paragraphs, including one at the end
		$pee = preg_replace('|<p>\s*?</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
		$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
		$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
		$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
		$pee = preg_replace('!<p>\s*(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)!', "$1", $pee);
		$pee = preg_replace('!(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*</p>!', "$1", $pee);
		if ($br) $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
		$pee = preg_replace('!(</?(?:table|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*<br />!', "$1", $pee);
		$pee = preg_replace('!<br />(\s*</?(?:p|li|div|th|pre|td|ul|ol)>)!', '$1', $pee);
		$pee = preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $pee);

		return $pee;
	}

	//-----------

	protected function txt_unpee($pee)
	{
		$pee = str_replace("\t", '', $pee);
		$pee = str_replace('</p><p>', '', $pee);
		$pee = str_replace('<p>', '', $pee);
		$pee = str_replace('</p>', "\n", $pee);
		$pee = str_replace('<br />', '', $pee);
		$pee = trim($pee);
		return $pee;
	}

	//----------------------------------------------------------
	// Attachments
	//----------------------------------------------------------
	
	protected function attach_rename()
	{
		// Incoming
		$id = JRequest::getInt( 'id', 0 );
		$name = trim(JRequest::getVar( 'name', '' ));

		// Ensure we have everything we need
		if ($id && $name != '') {
			$database =& JFactory::getDBO();
			
			$r = new ResourcesResource( $database );
			$r->load( $id );
			$r->title = $name;
			$r->store();
		}
		
		// Echo the name
		echo $name;
	}

	//-----------

	protected function attach_save()
	{
		$juser =& JFactory::getUser();
		$database =& JFactory::getDBO();
		
		// Incoming
		$pid = JRequest::getInt( 'pid', 0 );
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->attachments( $pid );
		}

		// Incoming file
		$file = JRequest::getVar( 'upload', '', 'files', 'array' );
		if (!$file['name']) {
			$this->setError( JText::_('CONTRIBUTE_NO_FILE') );
			$this->attachments( $pid );
			return;
		}
		
		// Make the filename safe
		jimport('joomla.filesystem.file');
		$file['name'] = JFile::makeSafe($file['name']);
		$file['name'] = str_replace(' ','_',$file['name']);

		// Instantiate a new resource object
		$row = new ResourcesResource( $database );
		if (!$row->bind( $_POST )) {
			$this->setError( $row->getError() );
			$this->attachments( $pid );
			return;
		}
		$row->title = ($row->title) ? $row->title : $file['name'];
		$row->introtext = $row->title;
		$row->created = date( 'Y-m-d H:i:s' );
		$row->created_by = $juser->get('id');
		$row->published = 1;
		$row->publish_up = date( 'Y-m-d H:i:s' );
		$row->publish_down = '0000-00-00 00:00:00';
		$row->standalone = 0;

		// Check content
		if (!$row->check()) {
			$this->setError( $row->getError() );
			$this->attachments( $pid );
			return;
		}
		// Store new content
		if (!$row->store()) {
			$this->setError( $row->getError() );
			$this->attachments( $pid );
			return;
		}
		
		if (!$row->id) {
			$row->id = $row->insertid();
		}
		
		// Build the path
		include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
		$listdir = ResourcesHtml::build_path( $row->created, $row->id, '' );
		$path = $this->_buildUploadPath( $listdir, '' );

		// Make sure the upload path exist
		if (!is_dir( $path )) {
			jimport('joomla.filesystem.folder');
			if (!JFolder::create( $path, 0777 )) {
				$this->setError( JText::_('UNABLE_TO_CREATE_UPLOAD_PATH') );
				$this->attachments( $pid );
				return;
			}
		}

		// Perform the upload
		if (!JFile::upload($file['tmp_name'], $path.DS.$file['name'])) {
			$this->setError( JText::_('ERROR_UPLOADING') );
		} else {
			// File was uploaded
			
			// Check the file type
			$row->type = $this->_getChildType($file['name']);
			
			// If it's a package (ZIP, etc) ...
			if ($row->type == 38) {
				/*jimport('joomla.filesystem.archive');
				
				// Extract the files
				if (!JArchive::extract( $file_to_unzip, $path )) {
					$this->setError( JText::_('Could not extract package.') );
				}*/
				require_once( JPATH_ROOT.DS.'administrator'.DS.'includes'.DS.'pcl'.DS.'pclzip.lib.php' );
		
				if (!extension_loaded('zlib')) {
					$this->setError( JText::_('ZLIB_PACKAGE_REQUIRED') );
				} else {
					// Check the table of contents and look for a Breeze viewer.swf file
					$isbreeze = 0;
					
					$zip = new PclZip( $path.DS.$file['name'] );
						
					$file_to_unzip = preg_replace('/(.+)\..*$/', '$1', $path.DS.$file['name']);
					
					if (($list = $zip->listContent()) == 0) {
						die('Error: '.$zip->errorInfo(true));
					}
					
					for ($i=0; $i<sizeof($list); $i++) 
					{
						if (substr($list[$i]['filename'], strlen($list[$i]['filename']) - 10, strlen($list[$i]['filename'])) == 'viewer.swf') {
							$isbreeze = $list[$i]['filename'];
							break;
						}
					}

					// It IS a breeze presentation
					if ($isbreeze) {
						// unzip the file
						$do = $zip->extract($file_path);
						if (!$do) {
							$this->setError( JText::_( 'UNABLE_TO_EXTRACT_PACKAGE' ) );
						} else {
							$row->path = $listdir.DS.$isbreeze;

							@unlink( $path.DS.$file['name'] );
						}
						$row->type = $this->_getChildType($row->path);
						$row->title = $isbreeze;
					}
				}
			}
		}
		
		if (!$row->path) {
			$row->path = $listdir.DS.$file['name'];
		}
		if (substr($row->path, 0, 1) == DS) {
			$row->path = substr($row->path, 1, strlen($row->path));
		}

		// Store new content
		if (!$row->store()) {
			$this->setError( $row->getError() );
			$this->attachments( $pid );
			return;
		}
		
		// Instantiate a ResourcesAssoc object
		$assoc = new ResourcesAssoc( $database );

		// Get the last child in the ordering
		$order = $assoc->getLastOrder( $pid );
		$order = ($order) ? $order : 0;
		
		// Increase the ordering - new items are always last
		$order = $order + 1;
		
		// Create new parent/child association
		$assoc->parent_id = $pid;
		$assoc->child_id = $row->id;
		$assoc->ordering = $order;
		$assoc->grouping = 0;
		if (!$assoc->check()) {
			$this->setError( $assoc->getError() );
		}
		if (!$assoc->store(true)) {
			$this->setError( $assoc->getError() );
		}
		$this->_rid = $pid;

		// Push through to the attachments view
		$this->attachments( $pid );
	}

	//-----------

	protected function attach_delete() 
	{
		$database =& JFactory::getDBO();
		
		// Incoming parent ID
		$pid = JRequest::getInt( 'pid', 0 );
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->attachments( $pid );
		}
		
		// Incoming child ID
		$id = JRequest::getInt( 'id', 0 );
		if (!$id) {
			$this->setError( JText::_('CONTRIBUTE_NO_CHILD_ID') );
			$this->attachments( $pid );
		}
		
		jimport('joomla.filesystem.folder');
	
		// Load resource info
		$row = new ResourcesResource( $database );
		$row->load( $id );
		
		// Get path and delete directories
		if ($row->path != '') {
			$listdir = $row->path;
		} else {
			// No stored path, derive from created date
			include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );		
			$listdir = ResourcesHtml::build_path( $row->created, $id, '' );
		}
		
		// Build the path
		$path = $this->_buildUploadPath( $listdir, '' );

		// Check if the folder even exists
		if (!is_dir($path) or !$path) { 
			$this->setError( JText::_('DIRECTORY_NOT_FOUND') ); 
		} else {
			// Attempt to delete the folder
			if (!JFolder::delete($path)) {
				$this->setError( JText::_('UNABLE_TO_DELETE_DIRECTORY') );
			}
		}
		
		// Delete associations to the resource
		$row->deleteExistence();
	
		// Delete resource
		$row->delete();

		// Push through to the attachments view
		$this->attachments( $pid );
	}
	
	//-----------

	protected function attachments( $id=null ) 
	{
		// Incoming
		if (!$id) {
			$id = JRequest::getInt( 'rid', 0 );
		}
		
		// Ensure we have an ID to work with
		if (!$id) {
			echo ContribtoolHtml::error( JText::_('CONTRIBUTE_NO_ID') );
			return;
		}
		
		$allowupload = JRequest::getInt( 'allowupload', 1 );
					
		// Initiate a resource helper class
		$database =& JFactory::getDBO();
		
		$helper = new ResourcesHelper( $id, $database );
		$helper->getChildren();
		
		// Get the app
		$app =& JFactory::getApplication();
		
		// get config
		$cparams =& JComponentHelper::getParams( 'com_contribute' );
		
		// Set the page title
		$pagetitle = JText::_(strtoupper($this->_name)).': '.JText::_('TASK_ATTACH');
			
		// Output HTML
		ContribtoolHtml::pageTop( 'com_contribute', $app, $pagetitle );
		ContribtoolHtml::attachments( $this->_option, $id, '', $helper->children, $cparams, $this->getError(), $allowupload );
		ContribtoolHtml::pageBottom();
	}
		
	
	//-----------

	protected function ss_reorder() 
	{
		$database =& JFactory::getDBO();
		
		// Incoming parent ID
		$pid = JRequest::getInt( 'pid', 0 );
		$version = JRequest::getVar( 'version', 'dev' );
	
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Get version id	
		$objV = new ToolVersion($database);	
		$vid = $objV->getVersionIdFromResource($pid, $version);
		
		if($vid == NULL) {
			$this->setError( JText::_('CONTRIBUTE_VERSION_ID_NOT_FOUND') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Incoming
		$file_toleft = JRequest::getVar( 'fl', '' );
		$order_toleft = JRequest::getInt( 'ol', 1);
		$file_toright = JRequest::getVar( 'fr', '' );
		$order_toright = JRequest::getInt( 'or', 0 );
		
		$neworder_toleft = ($order_toleft != 0) ? $order_toleft - 1 : 0;
		$neworder_toright = $order_toright + 1;
		
		
		// Instantiate a new screenshot object
		$ss = new ResourceScreenshot($database);
		$shot1 = $ss->getScreenshot($file_toright, $pid, $vid);
		$shot2 = $ss->getScreenshot($file_toleft, $pid, $vid);
		
		// Do we have information stored?
		if($shot1) {
			$ss->saveScreenshot( $file_toright, $pid, $vid, $neworder_toright );
		}
		else {
			$ss->saveScreenshot( $file_toright, $pid, $vid, $neworder_toright, true ); 
		}
		if($shot1) {
			$ss->saveScreenshot( $file_toleft, $pid, $vid, $neworder_toleft ); 
		}
		else {
			$ss->saveScreenshot( $file_toleft, $pid, $vid, $neworder_toleft, true ); 
		}
		
		$this->_rid = $pid;

		// Push through to the screenshot view
		$this->screenshots( $pid, $version );
	}
	
	//-----------

	protected function ss_edit()
	{
		$database =& JFactory::getDBO();
		
		// Incoming parent ID
		$pid = JRequest::getInt( 'pid', 0 );
		$version = JRequest::getVar( 'version', 'dev' );
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Incoming child ID
		$file = JRequest::getVar( 'filename', '' );
		if (!$file) {
			$this->setError( JText::_('CONTRIBUTE_NO_CHILD_ID') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Load resource info
		$row = new ResourcesResource( $database );
		$row->load( $pid );
		
		// Get version id	
		$objV = new ToolVersion($database);	
		$vid = $objV->getVersionIdFromResource($pid, $version);
		
		if($vid == NULL) {
			$this->setError( JText::_('CONTRIBUTE_VERSION_ID_NOT_FOUND') );
			$this->screenshots( $pid, $version );
			return;
		}
			
			
		// Build the path
		include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
		$listdir  = ResourcesHtml::build_path( $row->created, $pid, '' );
		$listdir .= DS.$vid;
		$wpath = $this->rconfig->get('uploadpath').DS.$listdir;
		$upath = $this->_buildUploadPath( $listdir, '' );
		
		// Instantiate a new screenshot object
		$ss = new ResourceScreenshot($database);
		$shot = $ss->getScreenshot($file, $pid, $vid);
		
		// Get the app
		$app =& JFactory::getApplication();
		
		// Set the page title
		$pagetitle = JText::_(strtoupper($this->_name)).': '.JText::_('TASK_EDIT_SS');
		$document =& JFactory::getDocument();
		$document->setTitle( $pagetitle );
		
		// Output HTML
		ContribtoolHtml::pageTop( 'com_contribute', $app, $pagetitle );
		ContribtoolHtml::ss_pop( $this->_option, $pid, $wpath, $upath, $file, $this->getError(), $version, $vid, $shot);
		ContribtoolHtml::pageBottom();		
		
	}
	
	//-----------

	protected function ss_save()
	{
		$database =& JFactory::getDBO();
		
		// Incoming parent ID
		$pid = JRequest::getInt( 'pid', 0 );
		$version = JRequest::getVar( 'version', 'dev' );
		$vid = JRequest::getInt( 'vid', 0 );
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Incoming
		$file = JRequest::getVar( 'filename', '' );
		$title = JRequest::getVar( 'title', '' );
			
		// Instantiate a new screenshot object
		$ss = new ResourceScreenshot($database);
		$shot = $ss->getScreenshot($file, $pid, $vid);
		$files = $ss->getFiles($pid, $vid);
		
		if($shot) {
			// update entry
			$ss->loadFromFilename( $file, $pid, $vid);
		} else {
			// make new entry
			$ss->versionid = $vid;
			$ordering = $ss->getLastOrdering($pid, $vid);
			$ss->ordering = ($ordering) ? $ordering + 1 : count($files) + 1; // put in the end
			$ss->filename = $file;
			$ss->resourceid = $pid;
		}
		$ss->title = preg_replace( '/"((.)*?)"/i', "&#147;\\1&#148;", $title );
		
		if (!$ss->store()) {
			$this->setError( $ss->getError() );
			return false;
		}
		// pop-up window will close through javascript
		
	}
	//-----------

	protected function ss_delete() 
	{
		$database =& JFactory::getDBO();
		
		// Incoming parent ID
		$pid = JRequest::getInt( 'pid', 0 );
		$version = JRequest::getVar( 'version', 'dev' );
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Incoming child ID
		$file = JRequest::getVar( 'filename', '' );
		if (!$file) {
			$this->setError( JText::_('CONTRIBUTE_NO_CHILD_ID') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
	
		// Load resource info
		$row = new ResourcesResource( $database );
		$row->load( $pid );
		
		// Get version id	
		$objV = new ToolVersion($database);	
		$vid = $objV->getVersionIdFromResource($pid, $version);
		
		if($vid == NULL) {
			$this->setError( JText::_('CONTRIBUTE_VERSION_ID_NOT_FOUND') );
			$this->screenshots( $pid, $version );
			return;
		}
			
		include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
			
		// Build the path
		$listdir  = ResourcesHtml::build_path( $row->created, $pid, '' );
		$listdir .= DS.$vid;
		$path = $this->_buildUploadPath( $listdir, '' );
		

		// Check if the folder even exists
		if (!is_dir($path) or !$path) { 
			$this->setError( JText::_('DIRECTORY_NOT_FOUND') ); 
			$this->screenshots( $pid, $version );
			return;
		} else {
			
			if(!JFile::exists($path.DS.$file)) {
				$this->screenshots( $pid, $version );
				return;
			}

			if (!JFile::delete($path.DS.$file)) {
				$this->setError( JText::_('UNABLE_TO_DELETE_FILE') );
				$this->screenshots( $pid, $version );
				return;
			}
			else {
				// Delete thumbnail
				$tn = ResourcesHtml::thumbnail($file);
				JFile::delete($path.DS.$tn);		
				
			
				// Instantiate a new screenshot object
				$ss = new ResourceScreenshot($database);
				$ss->deleteScreenshot($file, $pid, $vid);
			
			}
		}
		
		$this->_rid = $pid;

		// Push through to the screenshot view
		$this->screenshots( $pid, $version );
		
	}

	//-----------

	protected function ss_upload()
	{
		$juser =& JFactory::getUser();
		$database =& JFactory::getDBO();
		
		// Incoming
		$pid = JRequest::getInt( 'pid', 0 );
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		$version = JRequest::getVar( 'version', 'dev' );
		$title = JRequest::getVar( 'title', '' );
		$allowed = array('.gif','.jpg','.png','.bmp');
		$changing_version = JRequest::getInt( 'changing_version', 0 );
		if($changing_version) {
			// reload screen
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Get resource information
		$resource = new ResourcesResource( $database );
		$resource->load( $pid );

		// Incoming file
		$file = JRequest::getVar( 'upload', '', 'files', 'array' );
		if (!$file['name']) {
			$this->setError( JText::_('CONTRIBUTE_NO_FILE') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		
		// Make the filename safe
		jimport('joomla.filesystem.file');
		$file['name'] = JFile::makeSafe($file['name']);
		$file['name'] = str_replace(' ','_',$file['name']);
		$file['name'] = str_replace('-tn','',$file['name']);
		$file_basename = substr($file['name'], 0, strripos($file['name'], '.')); // strip extention
		$file_ext      = substr($file['name'], strripos($file['name'], '.'));
		
		
		// Make sure we have an allowed format
		if (!in_array(strtolower($file_ext), $allowed)) {
			$this->setError( JText::_('CONTRIBUTE_WRONG_FILE_FORMAT') );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Get version id	
		$objV = new ToolVersion($database);	
		$vid = $objV->getVersionIdFromResource($pid, $version);
		
		if($vid == NULL) {
			$this->setError( JText::_('CONTRIBUTE_VERSION_ID_NOT_FOUND') );
			$this->screenshots( $pid, $version );
			return;
		}
			
		// Instantiate a new screenshot object
		$row = new ResourceScreenshot($database);
		
		// Check if file with the same name already exists
		$files = $row->getFiles($pid, $vid);
		if(count($files) > 0) {
			$files = ContribtoolHelper::transform($files, 'filename');
			foreach ($files as $f) {
				if($f == $file['name']) {
					// append extra characters in the end
					$file['name'] = $file_basename.'_'.time().$file_ext;
					$file_basename = $file_basename.'_'.time();
				}
			}
		}
	
	
		$row->title = preg_replace( '/"((.)*?)"/i', "&#147;\\1&#148;", $title );
		$row->versionid = $vid;
		$ordering = $row->getLastOrdering($pid, $vid);
		$row->ordering = ($ordering) ? $ordering + 1 : count($files) + 1; // put in the end
		$row->filename = $file['name'];
		$row->resourceid = $pid;
	
		// Check content
		if (!$row->check()) {
			$this->setError( $row->getError() );
			$this->screenshots( $pid, $version );
			return;
		}
		
		// Build the path
		include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
		$listdir  = ResourcesHtml::build_path( $resource->created, $pid, '' );
		$listdir .= DS.$vid;
		$path = $this->_buildUploadPath( $listdir, '' );
		
		
		// Make sure the upload path exist
		if (!is_dir( $path )) {
			jimport('joomla.filesystem.folder');
			if (!JFolder::create( $path, 0777 )) {
				$this->setError( JText::_('UNABLE_TO_CREATE_UPLOAD_PATH') );
				$this->attachments( $pid );
				return;
			}
		}
		

		// Perform the upload
		if (!JFile::upload($file['tmp_name'], $path.DS.$file['name'])) {
			$this->setError( JText::_('ERROR_UPLOADING') );
		}
		else {
		
			// Store new content
			if (!$row->store()) {
				$this->setError( $row->getError() );
				$this->screenshots( $pid, $version );
				return;
			}
			
			if (!$row->id) {
				$row->id = $row->insertid();
			}
			
			// Create thumbnail
			
			$ss_height = isset($this->config->parameters['screenshot_maxheight']) && intval($this->config->parameters['screenshot_maxheight']) > 30 ? intval($this->config->parameters['screenshot_maxheight']) : '58';
			$ss_width= isset($this->config->parameters['screenshot_maxwidth']) && intval($this->config->parameters['screenshot_maxwidth']) > 80 ? intval($this->config->parameters['screenshot_maxwidth']) : '91';
		
			
			$tn = ResourcesHtml::thumbnail($file['name']);
			if($file_ext !='.swf') {
				$this->createThumb( $path.DS.$file['name'], $ss_width, $ss_height, $path, $tn );
			}
			else {
				//$this->createAnimThumb( $path.DS.$file['name'], $ss_width, $ss_height, $path, $tn );				
			}
		}

		// Store new content
		if (!$row->store()) {
			$this->setError( $row->getError() );
			$this->screenshots( $pid, $version );
			return;
		}
		
		$this->_rid = $pid;

		// Push through to the screenshot view
		$this->screenshots( $pid, $version );
	}
	
	//-----------
	
	function createAnimThumb( $tmpname, $maxwidth, $maxheight, $save_dir, $save_name )
    {
	
		$imorig = imagecreatefromjpeg(JPATH_ROOT.DS.'components'.DS.$this->_option.DS.'images'.DS.'anim.jpg');
		$x = imageSX($imorig);
        $y = imageSY($imorig);
       
        $yc = $y*1.555555;
        $d = $x>$yc?$x:$yc;
        $c = $d>$maxwidth ? $maxwidth/$d : $maxwidth;
        $av = $x*$c; 
        $ah = $y*$c; 
  
        $im = imagecreate($av, $ah);
        $im = imagecreatetruecolor($av,$ah);
    	if (imagecopyresampled($im,$imorig , 0,0,0,0,$av,$ah,$x,$y)) {
        if (imagegif($im, $save_dir.$save_name)) {
            return true;
		}
            else {
            return false;
			}
		}
	}
	
	//-----------
	
	function createThumb( $tmpname, $maxwidth, $maxheight, $save_dir, $save_name )
    {
   		$save_dir .= ( substr($save_dir,-1) != "/") ? DS : "";
        $gis       = getimagesize($tmpname);
    	$type       = $gis[2];
    	switch($type)
        {
        case "1": $imorig = imagecreatefromgif($tmpname); break;
        case "2": $imorig = imagecreatefromjpeg($tmpname);break;
        case "3": $imorig = imagecreatefrompng($tmpname); break;
		case "4": $imorig = imagecreatefromwbmp($tmpname); break;
        default:  $imorig = imagecreatefromjpeg($tmpname);
        } 

        $x = imageSX($imorig);
        $y = imageSY($imorig);
        if($gis[0] <= $maxwidth)
        {
        $av = $x;
        $ah = $y;
        }
         else
        {
            $yc = $y*1.555555;
            $d = $x>$yc?$x:$yc;
            $c = $d>$maxwidth ? $maxwidth/$d : $maxwidth;
              $av = $x*$c; 
              $ah = $y*$c; 

        }    
        $im = imagecreate($av, $ah);
        $im = imagecreatetruecolor($av,$ah);
    	if (imagecopyresampled($im,$imorig , 0,0,0,0,$av,$ah,$x,$y)) {
        if (imagegif($im, $save_dir.$save_name)) {
            return true;
		}
            else {
            return false;
			}
		}
    }
	//----------
	
	protected function copyss()
	{
		$database =& JFactory::getDBO();
		$juser =& JFactory::getUser();
		
		$version = JRequest::getVar( 'version', 'dev' );
		$rid     = JRequest::getInt( 'rid', 0 );
		$from    = $version=='dev' ? 'current' : 'dev';
		
		// get admin priviliges
		$this->authorize_admin();
		
		// Get version id	
		$objV = new ToolVersion($database);	
		$to = $objV->getVersionIdFromResource($rid, $version);
		$from = $objV->getVersionIdFromResource($rid, $from);
		
		// get tool id
		$obj = new Tool($database);	
		$toolid = $obj->getToolIdFromResource($rid);
		
		if($from == 0 or $to == 0 or $rid == 0) {
				echo ContribtoolHtml::alert( 'Missing ids' );
				exit();
				return;
		}
		
		if($toolid && $this->check_access($toolid, $juser, $this->_admin, 0) ) {
					if($this->transferScreenshots($from, $to, $rid)) {
						
						// Push through to the screenshot view
						$this->screenshots( $rid, $version );
					}
		
		}
		
		
	}
	
	//----------
	
	protected function movess()
	{
		
		$from    = JRequest::getInt( 'from', 0 );
		$to    	 = JRequest::getInt( 'to', 0 );
		$rid     = JRequest::getInt( 'rid', 0 );
		$version = JRequest::getVar( 'version', 'dev' );
		
			
		// get admin priviliges
		$this->authorize_admin();
		
		if($this->_admin or $this->_task=='copyss') {
		
			if($from == 0 or $to == 0 or $rid == 0) {
				echo ContribtoolHtml::alert( 'Missing ids' );
				exit();
				return;
			}
			
			if($this->transferScreenshots($from, $to, $rid)) {
				
				if($this->_task =='copyss') {
					
					$this->_rid = $rid;
	
					// Push through to the screenshot view
					$this->screenshots( $rid, $version );
				}
				else {
				echo 'Success!';
				}
			}
			else if($this->_task !='copyss') {
				echo 'Didn\'t work. There were some problems...';
			}
			
		}
		else {
			$this->_redirect = JRoute::_('index.php?option='.$this->_option);
			return;
		}
	}
	
	//----------
	
	/*
	protected function transferScreenshots($devid, $currentid, $rid)
	{
		$database =& JFactory::getDBO();
				
		// Get screenshot information
		$ss = new ResourceScreenshot($database);
		$shots = $ss->getFiles($rid, $devid);
		$total = $shots ? count($shots) : 0;
		
		echo $total;
		
		if($total) {
			// Get resource information
			$resource = new ResourcesResource( $database );
			$resource->load( $rid );
			
			// Build the path
			include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
			$listdir  	= ResourcesHtml::build_path( $resource->created, $rid, '' );
			$srcdir 	= $listdir.DS.$devid;
			$destdir 	= $listdir.DS.$currentid;
			$src 		= $this->_buildUploadPath( $srcdir, '' );
			$dest 		= $this->_buildUploadPath( $destdir, '' );
			
					
			// Make sure the path exist
			if (is_dir( $src )) {
				jimport('joomla.filesystem.folder');
				
				// Copy directory
				if (!JFolder::copy($src, $dest)) {
					return false;
				}
				else {
					// Delete source directory
					//JFolder::delete($src);
					
					// Update screenshot information for this resource
					$ss->updateFiles($rid, $devid, $currentid, $copy=1);
					
					return true;
				}			
				
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
		
	
	}
	*/
	protected function transferScreenshots($sourceid, $destid, $rid)
	{

		$database =& JFactory::getDBO();
				
		// Get resource information
		$resource = new ResourcesResource( $database );
		$resource->load( $rid );
		
		// Get screenshot information
		$ss = new ResourceScreenshot($database);
		$shots = $ss->getFiles($rid, $sourceid);
			
		// Build the path
		include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
		$listdir  	= ResourcesHtml::build_path( $resource->created, $rid, '' );
		$srcdir 	= $listdir.DS.$sourceid;
		$destdir 	= $listdir.DS.$destid;
		$src 		= $this->_buildUploadPath( $srcdir, '' );
		$dest 		= $this->_buildUploadPath( $destdir, '' );
		
		//echo $src;
		//echo $dest;
			
		jimport('joomla.filesystem.folder');
					
		// Make sure the path exist
		if (!is_dir( $src )) {
			if (!JFolder::create( $src, 0777 )) {
				$this->setError( JText::_('UNABLE_TO_CREATE_UPLOAD_PATH') );
				return false;
			}
		}
		
		// do we have files to transfer?
		$files = JFolder::files($src, '.', false, true, array());
		if(!empty($files)) {
				
			// Copy directory
			if (!JFolder::copy($src, $dest, '', true)) {
				return false;
			}
			else {
					// Delete source directory
					//JFolder::delete($src);
					
					// Update screenshot information for this resource
					$ss->updateFiles($rid, $sourceid, $destid, $copy=1);
					
					return true;
			}			
		}	
		
		return true;
		
	
	}
	
	//-----------

	protected function screenshots( $rid=NULL, $version=NULL ) 
	{
		$database =& JFactory::getDBO();
		
		// Incoming
		if (!$rid) {
			$rid = JRequest::getInt( 'rid', 0 );
		}
		if (!$version) {
			$version = JRequest::getVar( 'version', 'dev' );
		}
		//$version = 'current';
		
		// Ensure we have an ID to work with
		if (!$rid) {
			echo ContribtoolHtml::error( JText::_('CONTRIBUTE_NO_ID') );
			return;
		}
		// Get resource information
		$resource = new ResourcesResource( $database );
		$resource->load( $rid );
				
		// Get version id	
		$objV = new ToolVersion($database);	
		$vid = $objV->getVersionIdFromResource($rid, $version);
		
		// Do we have a published tool?
		$currentid = $objV->getCurrentVersionProperty ($resource->alias, 'id');
			
		// Get screenshot information for this resource
		$ss = new ResourceScreenshot($database);
		$shots = $ss->getScreenshots($rid, $vid);
		
		// Build paths
		include_once( JPATH_ROOT.DS.'components'.DS.'com_resources'.DS.'resources.html.php' );
		$path = ResourcesHtml::build_path( $resource->created, $rid, '' );
		$upath = JPATH_ROOT.$this->rconfig->get('uploadpath').$path;
		$wpath = $this->rconfig->get('uploadpath').$path;
		if($vid) {
			//$upath .= DS.ResourcesHtml::niceidformat( $vid);
			//$wpath .= DS.ResourcesHtml::niceidformat( $vid );
			$upath .= DS.$vid;
			$wpath .= DS.$vid;
		}
						
		// Get the app
		$app =& JFactory::getApplication();
		
		// get config
		$cparams =& JComponentHelper::getParams( 'com_contribute' );
		
		// Set the page title
		$pagetitle = JText::_(strtoupper($this->_name)).': '.JText::_('TASK_SS');
		
		// Output HTML
		ContribtoolHtml::pageTop( 'com_contribute', $app, $pagetitle );
		ContribtoolHtml::screenshots( $this->_option, $rid, $upath, $wpath, $cparams, $this->getError(), $version, $shots, $currentid);
		ContribtoolHtml::pageBottom();
	}

	//-----------
	
	private function _buildUploadPath( $listdir, $subdir='' ) 
	{
		if ($subdir) {
			// Make sure the path doesn't end with a slash
			if (substr($subdir, -1) == DS) { 
				$subdir = substr($subdir, 0, strlen($subdir) - 1);
			}
			// Ensure the path starts with a slash
			if (substr($subdir, 0, 1) != DS) { 
				$subdir = DS.$subdir;
			}
		}
		
		// Get the configured upload path
		$base_path = $this->rconfig->get('uploadpath');
		if ($base_path) {
			// Make sure the path doesn't end with a slash
			if (substr($base_path, -1) == DS) { 
				$base_path = substr($base_path, 0, strlen($base_path) - 1);
			}
			// Ensure the path starts with a slash
			if (substr($base_path, 0, 1) != DS) { 
				$base_path = DS.$base_path;
			}
		}
		
		// Make sure the path doesn't end with a slash
		if (substr($listdir, -1) == DS) { 
			$listdir = substr($listdir, 0, strlen($listdir) - 1);
		}
		// Ensure the path starts with a slash
		if (substr($listdir, 0, 1) != DS) { 
			$listdir = DS.$listdir;
		}
		// Does the beginning of the $listdir match the config path?
		if (substr($listdir, 0, strlen($base_path)) == $base_path) {
			// Yes - ... this really shouldn't happen
		} else {
			// No - append it
			$listdir = $base_path.$listdir;
		}

		// Build the path
		return JPATH_ROOT.$listdir.$subdir;
	}

	//-----------

	private function _getChildType($filename)
	{
		$filename_arr = explode('.',$filename);
		$ftype = end($filename_arr);
		$ftype = (strlen($ftype) > 3) ? substr($ftype, 0, 3) : $ftype;
		$ftype = strtolower($ftype);
	
		switch ($ftype) 
		{
			case 'mov': $type = 15; break;
			case 'swf': $type = 32; break;
			case 'ppt': $type = 35; break;
			case 'asf': $type = 37; break;
			case 'asx': $type = 37; break;
			case 'wmv': $type = 37; break;
			case 'zip': $type = 38; break;
			case 'tar': $type = 38; break;
			case 'pdf': $type = 33; break;
			default:    $type = 13; break;
		}
	
		return $type;
	}
	
	//-----------

	protected function reorder_attach() 
	{
		$database =& JFactory::getDBO();
		
		// Incoming
		$id = JRequest::getInt( 'id', 0 );
		$pid = JRequest::getInt( 'pid', 0 );

		// Ensure we have an ID to work with
		if (!$id) {
			$this->setError( JText::_('CONTRIBUTE_NO_CHILD_ID') );
			$this->attachments( $pid );
			return;
		}
		
		// Ensure we have a parent ID to work with
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->attachments( $pid );
			return;
		}

		$move = substr($this->_task, 0, (strlen($this->_task) - 1));

		// Get the element moving down - item 1
		$resource1 = new ResourcesAssoc( $database );
		$resource1->loadAssoc( $pid, $id );

		// Get the element directly after it in ordering - item 2
		$resource2 = clone( $resource1 );
		$resource2->getNeighbor( $move );

		switch ($move) 
		{
			case 'orderup':				
				// Switch places: give item 1 the position of item 2, vice versa
				$orderup = $resource2->ordering;
				$orderdn = $resource1->ordering;
				
				$resource1->ordering = $orderup;
				$resource2->ordering = $orderdn;
				break;
			
			case 'orderdown':
				// Switch places: give item 1 the position of item 2, vice versa
				$orderup = $resource1->ordering;
				$orderdn = $resource2->ordering;
				
				$resource1->ordering = $orderdn;
				$resource2->ordering = $orderup;
				break;
		}
		
		// Save changes
		$resource1->store();
		$resource2->store();
		
		// Push through to the attachments view
		$this->attachments( $pid );
	}
	//----------------------------------------------------------
	// contributors manager
	//----------------------------------------------------------

	protected function author_save($show=1)
	{
		// Incoming resource ID
		$id = JRequest::getInt( 'pid', 0 );
		if (!$id) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->authors( $id );
			return;
		}
		
		ximport('xprofile');
		
		$database =& JFactory::getDBO();
		
		// Incoming authors
		$authid = JRequest::getInt( 'authid', 0, 'post' );
		$authorsNewstr = JRequest::getVar( 'new_authors', '', 'post' );

		// Instantiate a resource/contributor association object
		$rc = new ResourcesContributor( $database );
		$rc->subtable = 'resources';
		$rc->subid = $id;
		
		// Get the last child in the ordering
		$order = $rc->getLastOrder( $id, 'resources' );
		$order = $order + 1; // new items are always last
		
		// Was there an ID? (this will come from the author <select>)
		if ($authid) {
			// Check if they're already linked to this resource
			$rc->loadAssociation( $authid, $id, 'resources' );
			if ($rc->authorid) {
				$this->setError( JText::sprintf('USER_IS_ALREADY_AUTHOR', $authid) );
			} else {
				// Perform a check to see if they have a contributors page. If not, we'll need to make one
				$xuser =& JUser::getInstance( $authid );
				if ($xuser) {
					$this->_author_check($xuser);

					// New record
					$rc->authorid = $authid;
					$rc->ordering = $order;
					$rc->role = '';
					$rc->createAssociation();

					$order++;
				}
			}
		}
			
		// Do we have new authors?
		if ($authorsNewstr) {
			// Turn the string into an array of usernames
			$authorsNew = split(',',$authorsNewstr);
			
			jimport('joomla.user.helper');
			
			// loop through each one
			for ($i=0, $n=count( $authorsNew ); $i < $n; $i++)
			{
				$cid = strtolower(trim($authorsNew[$i]));
			
				// Find the user's account info
				$uid = JUserHelper::getUserId($cid);
				if (!$uid) {
					$this->setError( JText::sprintf('UNABLE_TO_FIND_USER_ACCOUNT', $cid) );
					continue;
				}
				
				$xuser =& JUser::getInstance( $uid );
				if (!is_object($xuser)) {
					$this->setError( JText::sprintf('UNABLE_TO_FIND_USER_ACCOUNT', $cid) );
					continue;
				}

				$uid = $xuser->get('id');
		
				if (!$uid) {
					$this->setError( JText::sprintf('UNABLE_TO_FIND_USER_ACCOUNT', $cid) );
					continue;
				}
				
				// Check if they're already linked to this resource
				$rcc = new ResourcesContributor( $database );
				$rcc->loadAssociation( $uid, $id, 'resources' );
				if ($rcc->authorid) {
					$this->setError( JText::sprintf('USER_IS_ALREADY_AUTHOR', $cid) );
					continue;
				}
				
				$this->_author_check($xuser);
				
				// New record
				$rcc->subtable = 'resources';
				$rcc->subid = $id;
				$rcc->authorid = $uid;
				$rcc->ordering = $order;
				$rcc->role = '';
				$rcc->createAssociation();
				
				$order++;
			}
		}

		if ($show) {
			// Push through to the authors view
			$this->authors( $id );
		}
	}

	//-----------

	private function _author_check($xuser)
	{
		$xprofile = XProfile::getInstance($xuser->get('id'));
		if ($xprofile->get('givenName') == '' && $xprofile->get('middleName') == '' && $xprofile->get('surname') == '') {
			$bits = explode(' ', $xuser->get('name'));
			$xprofile->set('surname', array_pop($bits));
			if (count($bits) >= 1) {
				$xprofile->set('givenName', array_shift($bits));
			}
			if (count($bits) >= 1) {
				$xprofile->set('middleName', implode(' ',$bits));
			}
		}
	}

	//-----------

	protected function author_remove()
	{
		// Incoming
		$id  = JRequest::getInt( 'id', 0 );
		$pid = JRequest::getInt( 'pid', 0 );
		
		// Ensure we have a resource ID ($pid) to work with
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->authors();
			return;
		}
		
		// Ensure we have the contributor's ID ($id)
		if ($id) {
			$database =& JFactory::getDBO();
			
			$rc = new ResourcesContributor( $database );
			if (!$rc->deleteAssociation( $id, $pid, 'resources' )) {
				$this->setError( $rc->getError() );
			}
		}
		
		// Push through to the authors view
		$this->authors( $pid );
	}

	//-----------

	protected function reorder_author() 
	{
		$database =& JFactory::getDBO();
		
		// Incoming
		$id = JRequest::getInt( 'id', 0 );
		$pid = JRequest::getInt( 'pid', 0 );

		// Ensure we have an ID to work with
		if (!$id) {
			$this->setError( JText::_('CONTRIBUTE_NO_CHILD_ID') );
			$this->authors( $pid );
			return;
		}
		
		// Ensure we have a parent ID to work with
		if (!$pid) {
			$this->setError( JText::_('CONTRIBUTE_NO_ID') );
			$this->authors( $pid );
			return;
		}

		$move = substr($this->_task, 0, (strlen($this->_task) - 1));

		// Get the element moving down - item 1
		$author1 = new ResourcesContributor( $database );
		$author1->loadAssociation( $id, $pid, 'resources' );

		// Get the element directly after it in ordering - item 2
		$author2 = clone( $author1 );
		$author2->getNeighbor( $move );

		switch ($move) 
		{
			case 'orderup':				
				// Switch places: give item 1 the position of item 2, vice versa
				$orderup = $author2->ordering;
				$orderdn = $author1->ordering;
				
				$author1->ordering = $orderup;
				$author2->ordering = $orderdn;
				break;
			
			case 'orderdown':
				// Switch places: give item 1 the position of item 2, vice versa
				$orderup = $author1->ordering;
				$orderdn = $author2->ordering;
				
				$author1->ordering = $orderdn;
				$author2->ordering = $orderup;
				break;
		}
		
		// Save changes
		$author1->updateAssociation();
		$author2->updateAssociation();
		
		// Push through to the attachments view
		$this->authors( $pid );
	}

	//-----------

	protected function authors( $id=null ) 
	{
		$database =& JFactory::getDBO();
		
		// Incoming
		if (!$id) {
			$id = JRequest::getInt( 'rid', 0 );
		}
		
		$version = JRequest::getVar( 'version', 'dev' );
		
		// Ensure we have an ID to work with
		if (!$id) {
			echo ContribtoolHtml::error( JText::_('No resource ID found') );
			return;
		}
		
		// Initiate a resource helper class
		$database =& JFactory::getDBO();
		
		// Get all contributors of this resource
		$helper = new ResourcesHelper( $id, $database );
		if($version=='dev') {
			$helper->getCons();
		}
		else {
			$obj = new Tool( $database );
			$objV = new ToolVersion( $database );
			$toolname = $obj->getToolnameFromResource($id);
			$revision = $objV->getCurrentVersionProperty ($toolname, 'revision');
			$helper->getToolAuthors($toolname, $revision);
		}
		
		// Get a list of all existing contributors
		include_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_members'.DS.'members.class.php' );
		
		// Initiate a members object
		$mp = new MembersProfile( $database );
		
		$filters = array();
		$filters['search'] = '';
		$filters['show']   = '';
		$filters['index']  = '';
		$filters['limit']  = 'all';
		$filters['sortby'] = 'surname';
		$filters['authorized'] = false;
		
		// Get all members
		$rows = $mp->getRecords( $filters, false );

		// Get the app
		$app =& JFactory::getApplication();
		
		// Set the page title
		$pagetitle = JText::_(strtoupper($this->_name)).': '.JText::_('TASK_AUTHORS');
	
		// Output HTML
		ContribtoolHtml::pageTop( 'com_contribute', $app, $pagetitle );
		ContribtoolHtml::contributors( $id, $rows, $helper->_contributors, $this->_option, $this->getError(), $version );
		ContribtoolHtml::pageBottom();
	}


	//----------------------------------------------------------
	// misc.
	//----------------------------------------------------------
	
	private function check_access($toolid, $juser, $admin, $allow_siteadmins=1, $allow_authors=false) 
	{
		$database 	=& JFactory::getDBO();

		// Create a Tool object
		$obj = new Tool( $database );

		// allow to view if admin
		if($admin==2) { return true; }
		
		if($admin==1 && $allow_siteadmins) { return true; }

		// check if user in tool dev team
		$developers = $obj->getToolDevelopers($toolid);
		if($developers) {
			foreach($developers as $dv) {
				if($dv->uidNumber == $juser->get('id')) {
					return true;

				}
			}
		}

		// allow access to tool authors
		if($allow_authors) {

		}

		return false;

	}
	
	//--------------
	
	private function getFilters($admin)
	{
		// Query filters defaults
		$filters = array();
		$filters['sortby'] = trim(JRequest::getVar( 'sortby', '' ));
		$filters['filterby'] = trim(JRequest::getVar( 'filterby', 'all' ));
		$filters['search'] = trim(JRequest::getVar( 'search', '' ));

		if(!$admin) {	$filters['sortby'] = ($filters['sortby']) ? $filters['sortby'] : 'f.state, f.registered'; }
		else { $filters['sortby'] = ($filters['sortby']) ? $filters['sortby'] : 'f.state_changed DESC'; }

		// Paging vars
		$filters['limit'] = JRequest::getInt( 'limit', 1000 );
		$filters['start'] = JRequest::getInt( 'limitstart', 0, 'get' );


		// Return the array
		return $filters;
	}

	//-----------

	private function _getUsersGroups($groups)
	{
		$arr = array();
		if (!empty($groups)) {
			foreach ($groups as $group)
			{
				if ($group->regconfirmed) {
					$arr[] = $group->cn;
				}
			}
		}
		return $arr;
	}

	//-----------

	private function getGroups( $groups )
	{
		$juser =& JFactory::getUser();

		if (!$juser->get('guest')) {

			$ugs = XUserHelper::getGroups( $juser->get('id') );

			for ($i = 0; $i < count($ugs); $i++)
			{
				$public = XGroupHelper::get_groups('hub', false);

				$groups[$i]->cn  = $ugs[$i]->cn;
				$groups[$i]->description = $ugs[$i]->description;
			}
		}

		return $groups;
	}

	//------------
	
	private function authorize_admin($admin = 0, $groups=array())
	{

		// Check if they're a site admin (from LDAP)
		$xuser =& XFactory::getUser();
		if (is_object($xuser)) {
			$app =& JFactory::getApplication();
			if (in_array(strtolower($app->getCfg('sitename')), $xuser->get('admin'))) {
				$admin = 1;
			}
		}

		$juser =& JFactory::getUser();
		// Check if they're a site admin (from Joomla)
		if ($juser->authorize($this->_option, 'manage')) {
			$admin = 1;
		}

		// Was a specific group set in the config?
		$admingroup = isset($this->config->parameters['admingroup']) ? trim($this->config->parameters['admingroup']) : 'apps';
		if ($admingroup) {

			// Check if they're a member of admin group
			$ugs = XUserHelper::getGroups( $juser->get('id') );
			if ($ugs && count($ugs) > 0) {
				foreach ($ugs as $ug)
				{
					if ($ug->cn == $admingroup) {
						$admin = 2;
					}
					if($ug->manager) {
						$groups[]=$ug->cn;
					}
				}
			}

		}
		$this->_groups = $groups;
		$this->_admin = $admin;
	}
	
	//-----------

	public function txt_clean( &$text ) 
	{
		$text = preg_replace( '/{kl_php}(.*?){\/kl_php}/s', '', $text );
		$text = preg_replace( '/{.+?}/', '', $text );
		$text = preg_replace( "'<style[^>]*>.*?</style>'si", '', $text );
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", '', $text );
		//$text = preg_replace( '/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2', $text );
		$text = preg_replace( '/<!--.+?-->/', '', $text );
		//$text = nl2br( $text );
		//$text = str_replace( '<br>', '<br />', $text );
		return $text;
	}

}

?>
