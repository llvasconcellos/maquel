<?php
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' ); 
/**
*
* @version $Id: admin.function_form.php 617 2007-01-04 19:43:08Z soeren_nb $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2005 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
mm_showMyFileName( __FILE__ );

$function_id = mosGetParam( $_REQUEST, 'function_id');
$module_id = mosGetParam( $_REQUEST, 'module_id');
$option = empty($option)?mosgetparam( $_REQUEST, 'option', 'com_virtuemart'):$option;

if (!empty($function_id)) {
  $q = "SELECT * from #__{vm}_function where function_id='$function_id'";
  $db->query($q);
  $db->next_record();
}
//First create the object and let it print a form heading
$formObj = &new formFactory( $VM_LANG->_PHPSHOP_FUNCTION_FORM_LBL );
//Then Start the form
$formObj->startForm();
?> 
  <table class="adminform">
    <tr> 
      <td colspan="2">&nbsp;</td>
    </tr>
    <tr> 
      <td width="24%" align="right"><?php echo $VM_LANG->_PHPSHOP_FUNCTION_FORM_NAME ?>:</td>
      <td width="76%"> 
        <input type="text" class="inputbox" name="function_name" value="<?php $db->sp("function_name") ?>" />
      </td>
    </tr>
    <tr> 
      <td width="24%" align="right"><?php echo $VM_LANG->_PHPSHOP_FUNCTION_FORM_CLASS ?>:</td>
      <td width="76%"> 
        <input type="text" class="inputbox" name="function_class" value="<?php $db->sp("function_class") ?>" />
      </td>
    </tr>
    <tr> 
      <td width="24%" align="right"><?php echo $VM_LANG->_PHPSHOP_FUNCTION_FORM_METHOD ?>:</td>
      <td width="76%"> 
        <input type="text" class="inputbox" name="function_method" value="<?php $db->sp("function_method") ?>" />
      </td>
    </tr>
    <tr> 
      <td width="24%" align="right"><?php echo $VM_LANG->_PHPSHOP_FUNCTION_FORM_PERMS ?>:</td>
      <td width="76%"> 
        <input type="text" class="inputbox" name="function_perms" value="<?php $db->sp("function_perms") ?>" />
      </td>
    </tr>
    <tr> 
      <td valign="top" colspan="2" align="right">&nbsp; </td>
    </tr>
    <tr> 
      <td valign="top" align="right"><?php echo $VM_LANG->_PHPSHOP_FUNCTION_FORM_DESCRIPTION ?>:</td>
      <td valign="top">&nbsp;</td>
    </tr>
    <tr align="center"> 
      <td valign="top" colspan="2"> 
        <textarea name="function_description" cols="60" rows="10"><?php $db->sp("function_description") ?></textarea>
      </td>
    </tr>
   
  </table>
<?php
// Add necessary hidden fields
$formObj->hiddenField( 'function_id', $function_id );
$formObj->hiddenField( 'module_id', $module_id );

$funcname = (!empty( $function_id )) ? "functionUpdate" : "functionAdd";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, 'admin.function_list', $option );

?>