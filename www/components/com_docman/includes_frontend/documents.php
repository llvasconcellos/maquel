<?php
/**
 * DOCman 1.4.x - Joomla! Document Manager
 * @version $Id: documents.php 638 2008-03-01 12:49:09Z mjaz $
 * @package DOCman_1.4
 * @copyright (C) 2003-2008 The DOCman Development Team
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.joomlatools.org/ Official website
 **/
defined('_VALID_MOS') or die('Restricted access');

include_once dirname(__FILE__) . '/documents.html.php';

require_once($_DOCMAN->getPath('classes', 'mambots'));
require_once($_DOCMAN->getPath('classes', 'model'));
require_once($_DOCMAN->getPath('classes', 'theme'));
require_once($_DOCMAN->getPath('classes', 'utils'));
include_once($_DOCMAN->getPath('classes', 'params'));
mosArrayToInts( $cid );

function fetchDocument($id)
{
    global $_DOCMAN, $_DMUSER;




    // onFetchDocument event, type = details
    $bot = new DOCMAN_mambot('onFetchDocument');
    $bot->setParm('id' , $id);
    $bot->copyParm('type' , 'details');
    $bot->trigger();
    if ($bot->getError()) {
        _returnTo('cat_view', $bot->getErrorMsg());
    }

    // document
    $doc = & DOCMAN_Document::getInstance($id);

    // process content mambots
    DOCMAN_Utils::processContentBots( $doc, 'dmdescription' );

    return HTML_DMDocuments::displayDocument(
    	$doc->getLinkObject(),
        $doc->getPathObject(),
        $doc->getDataObject()
        );
}

function fetchDocumentList($catid)
{
    global $_DOCMAN, $_DMUSER, $ordering, $direction, $limit, $limitstart;

    if(!$catid) {
    	return;
    }

    $rows = DOCMAN_Docs::getDocsByUserAccess($catid, $ordering, $direction, $limit, $limitstart);
    if( !is_array($rows) ) {
    	$rows = array();
    }
    $params = array(
    	'limit' 	 => $limit,
    	'limitstart' => $limitstart,
    );

    // create orderby object
    $links = array();
    $links['name'] = _taskLink('cat_view', $catid, array_merge($params, array('order' => 'name', 'dir' => $direction)));
    $links['date'] = _taskLink('cat_view', $catid, array_merge($params, array('order' => 'date', 'dir' => $direction)));
    $links['hits'] = _taskLink('cat_view', $catid, array_merge($params, array('order' => 'hits', 'dir' => $direction)));

    if ($direction == 'ASC') {
        $links['dir'] = _taskLink('cat_view', $catid, array_merge($params, array('order' => $ordering, 'dir' => 'DESC')));
    } else {
        $links['dir'] = _taskLink('cat_view', $catid, array_merge($params, array('order' => $ordering, 'dir' => 'ASC')));
    }

    //set pathway information
    $pathway = new StdClass();
    $pathway->links = $links;

    //set order information
    $order = new StdClass();
    $order->links 		= $links;
    $order->direction 	= $direction;
    $order->orderby 	= $ordering;
    $order->limit   	= $limit;
    $order->limitstart	= $limitstart;

    $items = array();
    foreach($rows as $row) {


        // onFetchDocument event, type = list
        $bot = new DOCMAN_mambot('onFetchDocument');
        $bot->setParm('id' , $row->id);
        $bot->copyParm('type' , 'list');
        $bot->trigger();
        if ($bot->getError()) {
            _returnTo('cat_view', $bot->getErrorMsg());
        }

        // load doc
        $doc = & DOCMAN_Document::getInstance($row->id);

        // process content mambots
        DOCMAN_Utils::processContentBots( $doc, 'dmdescription' );

        $item = new StdClass();
        $item->buttons = &$doc->getLinkObject();
        $item->paths = &$doc->getPathObject();
        $item->data = &$doc->getDataObject();

        $items[] = $item;
    }

    return HTML_DMDocuments::displayDocumentList($order, $items);
}

function fetchEditDocumentForm($uid, $filename = null, $catid = 0)
{
    global $database, $mosConfig_absolute_path;
    global $_DOCMAN, $_DMUSER;

    $doc = new mosDMDocument($database);

    if ($uid) {
     	$doc->load($uid); 		//Load the document

     	 //check user permissions
    	$err = $_DMUSER->canPreformTask($doc, 'Edit');
    	if($err)	{
    		_returnTo('cat_view', $err, $doc->catid);
   		 }
    } else {
        $doc->init_record(); 	//Initialise a document

         //check user permissions
    	$err = $_DMUSER->canPreformTask($doc, 'Upload');
    	if($err)	{
    		_returnTo('cat_view', $err, $doc->catid);
   		}
    }

    //checkout the document
   	$doc->checkout($_DMUSER->userid);

   	// Set document filename
   	if (!is_null($filename)) {
        $filename = DOCMAN_Utils::safeDecodeURL($filename);
       	$doc->dmfilename = $filename;
   	}

   	// Set document url
    $prefix = substr($doc->dmfilename , 0, _DM_DOCUMENT_LINK_LNG);
    if (strcasecmp($prefix, _DM_DOCUMENT_LINK) == 0) {
        $doc->dmlink     = substr($doc->dmfilename, _DM_DOCUMENT_LINK_LNG);
        $doc->dmfilename = _DM_DOCUMENT_LINK ;
    }

    $lists = array();

     // Set filename
    $lists['dmfilename'] = filesSelectList($doc);

    // Built category select list
    $options = array(mosHTML::makeOption('0', _DML_SELECT_CAT));
    if ($uid) {
        $lists['catid'] = dmHTML::categoryList($doc->catid, "", $options);
    } else {
        $lists['catid'] = dmHTML::categoryList($catid, "", $options);
    }

    // Build select lists
    $lists['approved']  = mosHTML::yesnoRadioList('approved', 'class="inputbox"', $doc->approved);
    $lists['published'] = mosHTML::yesnoRadioList('published', 'class="inputbox"', $doc->published);

    $lists['viewer']     = dmHTML::viewerList($doc, 'dmowner');
   	$lists['maintainer'] = dmHTML::maintainerList($doc, 'dmmantainedby');

    $lists['licenses']         = licenseSelectList($doc);
    $lists['licenses_display'] = licenseDisplayList($doc);

    // Built image list
    $lists['dmthumbnail'] = dmHTML::imageList('dmthumbnail', $doc->dmthumbnail);
    $lists['dmthumbnail_preview'] = $doc->dmthumbnail;

    // Find lastupdate user
    $last = array();
    if ($doc->dmlastupdateby > _DM_PERMIT_USER) {
        $database->setQuery("SELECT id, name "
             . "\n FROM #__users "
             . "\n WHERE id=" . (int) $doc->dmlastupdateby);
        $last = $database->loadObjectList();
    } else {
        $last[0]->name = "Super Administrator";
    }

    // Find createdby user
    $created = array();
    if ($doc->dmsubmitedby > _DM_PERMIT_USER) {
        $database->setQuery("SELECT id, name "
             . "\n FROM #__users "
             . "\n WHERE id=". (int) $doc->dmsubmitedby);
        $created = $database->loadObjectList();
    } else {
        $created[0]->name = "Super Administrator";
    }

    // update 'dmlastupdateon'
    $doc->dmlastupdateon = date( "Y-m-d H:i:s" );

     // Params definitions
    $params = null;
    $params_path = $mosConfig_absolute_path . '/administrator/components/com_docman/docman.params.xml';
	if(file_exists($params_path)) {
		$params =& new dmParameters( $doc->attribs, $params_path , 'params' );
	}

    /* ------------------------------ *
     *   MAMBOT - Setup All Mambots   *
     * ------------------------------ */
    $prebot = new DOCMAN_mambot('onBeforeEditDocument');
    $prebot->setParm('document' , $doc);
    $prebot->setParm('filename' , $filename);
    $prebot->setParm('user' , $_DMUSER);

    if (!$uid) {
        $prebot->copyParm('process' , 'new document');
    } else {
        $prebot->copyParm('process' , 'edit document');
    }

    $prebot->trigger();

    if ($prebot->getError()) {
        _returnTo('cat_view', $prebot->getErrorMsg());
    }

   return HTML_DMDocuments::editDocumentForm($doc, $lists, $last, $created, $params);
}

function fetchMoveDocumentForm($uid)
{
    global $database, $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($uid);

    //check user permissions
    $err = $_DMUSER->canPreformTask($doc, 'Move');
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

    $doc = new DOCMAN_Document($uid);

    // category select list
    $options = array(mosHTML::makeOption('0', _DML_SELECT_CAT));
    $lists['categories'] = dmHTML::categoryList($doc->getData('catid'), "", $options);

    return HTML_DMDocuments::moveDocumentForm($lists,
        $doc->getLinkObject(),
        $doc->getPathObject(),
        $doc->getDataObject());
}

function moveDocumentProcess($uid)
{
    DOCMAN_token::check() or die('Invalid Token');

    global $database, $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($uid);

    //check user permissions
    $err = $_DMUSER->canPreformTask($doc, 'Move');
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

    // get the id of the category to move the document to
    $move = (int) mosGetParam($_POST, 'catid');

    // preform move
    $doc = new mosDMDocument($database);
    $doc->move(array($uid), $move);

    _returnTo('cat_view' , _DML_DOCMOVED , $move);
}

function updateDocumentProcess($uid)
{
    DOCMAN_token::check() or die('Invalid Token');

    $step   = (int) mosGetParam($_REQUEST, 'step', 1);
	$uploaded = mosGetParam(DOCMAN_Utils::stripslashes($_FILES) , 'upload');
	uploadFile($step, $uploaded);
}

function approveDocument($cid, $approve = 1)
{
    DOCMAN_token::check() or die('Invalid Token');

    global $database, $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($cid[0]);

    //check user permissions
    $task = $approve ? 'Approve' : 'UnApprove';
    $err = $_DMUSER->canPreformTask($doc, $task);
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

	//approve the document
    $doc->approve($cid, $approve);
    $doc->load($cid[0]);

    _returnTo('cat_view', '', $doc->catid);
}

function publishDocument($cid, $publish = 1)
{
    DOCMAN_token::check() or die('Invalid Token');

    global $database, $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($cid[0]);

    //check user permissions
    $task = $publish ? 'Publish' : 'UnPublish';
   	$err = $_DMUSER->canPreformTask($doc, $task);
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

    //publish the document
    $doc->publish($cid, $publish);

    _returnTo('cat_view', '', $doc->catid);
}

function saveDocument($uid)
{
    DOCMAN_token::check() or die('Invalid Token');

    global $database, $_DMUSER ;

    //fetch params
    $params = mosGetParam( $_REQUEST, 'params', '' );
	if (is_array( $params )) {
		$txt = array();
		foreach ($params as $k=>$v) {
			$txt[] = "$k=$v";
		}
		$_POST['attribs'] = implode( "\n", $txt );
	}

    $doc = new mosDMDocument($database);
    $doc->load($uid); // Load from id
    $doc->bind(DOCMAN_Utils::stripslashes($_POST));

    /* ------------------------------ *
     *   MAMBOT - Setup All Mambots   *
     * ------------------------------ */
    $logbot = new DOCMAN_mambot('onLog');
    $postbot = new DOCMAN_mambot('onAfterEditDocument');
    $logbot->setParm('document' , $doc);
    $logbot->setParm('file' , $_POST['dmfilename']);
    $logbot->setParm('user' , $_DMUSER);

    if (!$uid) {
        $logbot->copyParm('process' , 'new document');
    } else {
        $logbot->copyParm('process' , 'edit document');
    }
    $logbot->copyParm('new' , !$uid);
    $postbot->setParmArray($logbot->getParm());

    $postbot->trigger();
    if ($postbot->getError()) {
      	$logbot->copyParm('msg' , $postbot->getErrorMsg());
       	$logbot->copyParm('status' , 'LOG_ERROR');
        $logbot->trigger();
        _returnTo('cat_view', $postbot->getErrorMsg());
   	}

    // let's indicate last update information to store
    if ($doc->save()) {
        $logbot->copyParm('msg' , 'Document saved');
        $logbot->copyParm('status' , 'LOG_OK');
        $logbot->trigger();

        // if submited for the first time lets do auto-approve/publish operations
        if (!$uid) {
            autoApprove($doc);
            autoPublish($doc);
        }
        /* removed $message: undefined
         * original code:
         * _returnTo('cat_view', _DML_THANKSDOCMAN . $message ? "<br />" . $message : '', $doc->catid);
         */
        _returnTo('cat_view', _DML_THANKSDOCMAN, $doc->catid);
    }
    // doc->save failed. Log error
    $logbot->copyParm('msg' , $doc->getError());
    $logbot->copyParm('status' , 'LOG_ERROR');
    $logbot->trigger();

    _returnTo('cat_view', _DML_PROBLEM_SAVING_DOCUMENT);
}

function cancelDocument($gid)
{
    global $database;

    $uid = (int) mosGetParam($_REQUEST, "id", "");

    if (! $uid) {
        _returnTo('cat_view', _DML_OP_CANCELED);
    }

    $doc = new mosDMDocument($database);
    $doc->load($uid);

    if ($doc->cancel()) {
        _returnTo('cat_view', _DML_OP_CANCELED, $doc->catid);
    }

    _returnTo('cat_view', _DML_OP_CANCELED);
}

function checkinDocument($uid)
{
    global $database, $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($uid);

    //check user permissions
    $err = $_DMUSER->canPreformTask($doc, 'CheckIn');
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

    //checkin the document
    $doc->checkin();
    $msg = "&quot;" . $doc->dmname . "&quot; " . _DML_CHECKED_IN;

    _returnTo('cat_view', $msg, $doc->catid);
}

function checkoutDocument($uid)
{
    global $database, $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($uid);

    //check user permissions
    $err = $_DMUSER->canPreformTask($doc, 'CheckOut');
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

    //checkout the document
    $doc->checkout($_DMUSER->userid);
    $msg = "&quot;" . $doc->dmname . "&quot; " . _DML_CHECKED_OUT;

    _returnTo('cat_view' , $msg , $doc->catid);
}

function resetDocument($uid)
{
    global $database, $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($uid);

    //check user permissions
    $err = $_DMUSER->canPreformTask($doc, 'Reset');
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

    //reset the document counter
    $doc->dmcounter = 0;
    $doc->store();

    _returnTo('cat_view' , _DML_RESET_COUNTER , $doc->catid);
}

function deleteDocument($uid)
{
    DOCMAN_token::check() or die('Invalid Token');

    global $database,  $_DMUSER;

    $doc = new mosDMDocument($database);
    $doc->load($uid);

    //check user permissions
    $err = $_DMUSER->canPreformTask($doc, 'Delete');
    if($err)	{
    	_returnTo('cat_view', $err, $doc->catid);
    }

    //delete the docmument
    $doc->remove(array($uid));
    _returnTo('cat_view' , _DML_DOCDELETED , $doc->catid);
}

function autoApprove($doc) {
    global $database, $_DOCMAN;
    $approve = $_DOCMAN->getCfg('user_approve');
    if ($approve == _DM_PERMIT_EVERYONE) {
        //$doc = new mosDMDocument($database);
        $doc->approve(array($doc->id), 1);
    }
}

function autoPublish($doc)
{
    global $database, $_DOCMAN;

    $approve = $_DOCMAN->getCfg('user_publish');
    if ($approve == _DM_PERMIT_EVERYONE) {
        //$doc = new mosDMDocument($database);
        $doc->publish(array($doc->id), 1);
    }
}

function licenseSelectList(&$doc)
{
	global $database;

    $database->setQuery("SELECT id, name FROM #__docman_licenses ORDER BY name ASC");
    $result = $database->loadObjectList();

    $options = array();
    $options[] = mosHTML::makeOption('0', _DML_NO_LICENSE);

    foreach($result as $license) {
        $options[] = mosHTML::makeOption($license->id, $license->name);
    }

    $selected = $doc->dmlicense_id;

    $std_opt = 'class="inputbox" size="1"';
    $list = mosHTML::selectList($options,'dmlicense_id', $std_opt, 'value', 'text', $selected);
	return $list;
}

function filesSelectList(&$doc)
{
	/*
	 * PROGRAMMER NOTE:
     * Do NOT use FULL url for description on links. This could expose passwords
     * (Not a wise idea though they could get them elsewhere in the system...)
     */

    $options  = array();
    $selected = null;

    if (!$doc->id)
    {
        if ($doc->dmfilename == _DM_DOCUMENT_LINK)
        {
            //create options
            $options[] = mosHTML::makeOption(_DM_DOCUMENT_LINK , _DML_LINKED);
            $selected = _DM_DOCUMENT_LINK;

            //change document data
            $parsed_url = parse_url($doc->dmlink);

            $doc->dmname = _DML_LINKTO .
            (isset($parsed_url['path']) ? basename($parsed_url['path']) : $parsed_url['host']);

            $doc->dmdescription = "\n"
             . _DML_DOCLINKTO . ':'
             . $parsed_url['scheme'] . '://'
             . $parsed_url['host']
             . (isset($parsed_url['path'])  ? $parsed_url['path']  : '')
             . (isset($parsed_url['query']) ? $parsed_url['query'] : '')
             . "\n\n"
             . _DML_DOCLINKON . ':' . strftime("%a, %Y-%b-%d %R") ;
        }
        else
        {
            //create options
            $options[] = mosHTML::makeOption($doc->dmfilename);
            $selected = $doc->dmfilename;

            //change document data
             $doc->dmname = substr($doc->dmfilename, 0, strrpos($doc->dmfilename, "."));
        }
    }
    else
    {
       //create options
        $options[] = mosHTML::makeOption('', _DML_SELECT_FILE);

        if (!is_null($doc->id)) {
            $options[] = mosHTML::makeOption($doc->dmfilename, _DML_CURRENT . ': ' . $doc->dmfilename);
        }

        $files = DOCMAN_docs::getFilesByUserAccess();
        foreach($files as $file) {
            if (is_null($doc->id) || $file->dmfilename != $doc->dmfilename) {
                $options[] = mosHTML::makeOption($file->dmfilename);
            }
        }

        if (count($options) < 2) {
            //_returnTo('upload', _DML_YOU_MUST_UPLOAD);
        }

        $selected = $doc->dmfilename;
    }

    $std_opt = 'class="inputbox" size="1"';
    $list = mosHTML::selectList($options , 'dmfilename', $std_opt, 'value', 'text', $selected);
	return $list;
}

function licenseDisplayList(&$doc)
{
	$options = array();
	$options[] = mosHTML::makeOption('0', _DML_NO);
    $options[] = mosHTML::makeOption('1', _DML_YES);

    $selected = $doc->dmlicense_display;

    $std_opt = 'class="inputbox" size="1"';
    $list = mosHTML::selectList($options, 'dmlicense_display', $std_opt, 'value', 'text', $selected);
	return $list;
}