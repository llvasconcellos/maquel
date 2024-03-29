<?php
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );
/**
* The configuration file for VirtueMart
*
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) 2004-2007 Soeren Eberhardt. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

global $mosConfig_absolute_path,$mosConfig_live_site;

if( !class_exists( 'jconfig' )) {
	$global_lang = $GLOBALS['mosConfig_lang'];
	@include( dirname( __FILE__ ).'/../../../configuration.php' );	
	$GLOBALS['mosConfig_lang'] = $mosConfig_lang = $global_lang;
}

// Check for trailing slash
if( $mosConfig_live_site[strlen( $mosConfig_live_site)-1] == '/' ) {
	$app = '';
}
else {
	$app = '/';
}
// these path and url definitions here are based on the mambo configuration
define( 'URL', $mosConfig_live_site.$app );
define( 'SECUREURL', 'http://localhost/maquel/' );

if ( @$_SERVER['HTTPS'] == 'on' ) {
	define( 'IMAGEURL', SECUREURL .'components/com_virtuemart/shop_image/' );
} else {
	define( 'IMAGEURL', URL .'components/com_virtuemart/shop_image/' );
}
define( 'COMPONENTURL', URL .'administrator/components/com_virtuemart/' );
define( 'ADMINPATH', $mosConfig_absolute_path.'/administrator/components/com_virtuemart/' );
define( 'CLASSPATH', ADMINPATH.'classes/' );
define( 'PAGEPATH', ADMINPATH.'html/' );
define( 'IMAGEPATH', $mosConfig_absolute_path.'/components/com_virtuemart/shop_image/' );

define('PSHOP_IS_OFFLINE', '');
define('PSHOP_OFFLINE_MESSAGE', '<h2>Our Shop is currently down for maintenance.</h2> Please check back again soon.');
define('USE_AS_CATALOGUE', '1');
define('VM_TABLEPREFIX', 'vm');
define('VM_PRICE_SHOW_PACKAGING_PRICELABEL', '');
define('VM_PRICE_SHOW_INCLUDINGTAX', '');
define('VM_PRICE_ACCESS_LEVEL', '0');
define('VM_SILENT_REGISTRATION', '');
define('VM_BROWSE_ORDERBY_FIELD', 'product_name');
define('VM_ONCHECKOUT_SHOW_LEGALINFO', '');
define('VM_ONCHECKOUT_LEGALINFO_SHORTTEXT', '<h5>Pol�tica de devolu��es</h5>

Se o produto n�o corresponder �s suas expectativas, assiste-lhe o direito de dissolver o contrato nos 7 dias posteriores � entrega da mercadoria. Para o efeito, dever� proceder ao envio de carta registada com Aviso de Recep��o, comunicando ao vendedor a vontade de dissolver o contrato.
O consumidor disp�e de um prazo de 15 dias �teis a contar da recep��o da mercadoria para devolver a mesma � entidade 
fornecedora, efetuando esta a sua troca ou reembolso. Esta garantia aplica-se a produtos em estado de novo, sem 
utiliza��es e na embalagem original.
No caso do direito de resolu��o ser utilizado, os montantes que j� tiverem sido pagos pelo consumidor ser�o reembolsados no prazo de 15 dias a contar da data da recep��o das mercadorias devolvidas. 
Para mais informa��es sobre cancelamento de pedidos e devolu��o de produtos, veja a p�gina <a href="%s" onclick="%s" target="_blank">Nossa pol�tica de devolu��es</a>.');
define('VM_ONCHECKOUT_LEGALINFO_LINK', '');
define('ENABLE_DOWNLOADS', '');
define('DOWNLOAD_MAX', '3');
define('DOWNLOAD_EXPIRE', '432000');
define('ENABLE_DOWNLOAD_STATUS', 'C');
define('DISABLE_DOWNLOAD_STATUS', 'X');
define('DOWNLOADROOT', 'C:/ServidorWeb/www/maquel/');
define('_SHOW_PRICES', '');
define('ORDER_MAIL_HTML', '1');
define('HOMEPAGE', 'shop.index');
define('FLYPAGE', 'shop.flypage');
define('CATEGORY_TEMPLATE', 'browse_1');
define('PRODUCTS_PER_ROW', '1');
define('ERRORPAGE', 'shop.error');
define('NO_IMAGE', '/ps_image/noimage.gif');
define('SEARCH_ROWS', '30');
define('SEARCH_COLOR_1', '#f9f9f9');
define('SEARCH_COLOR_2', '#f0f0f0');
define('DEBUG', '');
define('SHOWVERSION', '');
define('PSHOP_ADD_TO_CART_STYLE', 'add-to-cart_orange.gif');
define('TAX_VIRTUAL', '');
define('TAX_MODE', '1');
define('MULTIPLE_TAXRATES_ENABLE', '');
define('PAYMENT_DISCOUNT_BEFORE', '');
define('PSHOP_ALLOW_REVIEWS', '');
define('MUST_AGREE_TO_TOS', '');
define('PSHOP_AGREE_TO_TOS_ONORDER', '');
define('LEAVE_BANK_DATA', '');
define('CAN_SELECT_STATES', '');
define('SHOW_CHECKOUT_BAR', '1');
define('CHECKOUT_STYLE', '1');
define('CHECK_STOCK', '');
define('ENCODE_KEY', 'VirtueMart_IsCool');
define('NO_SHIPPING', '1');
define('NO_SHIPTO', '');
define('AFFILIATE_ENABLE', '');
define('PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS', '');
define('PSHOP_IMG_RESIZE_ENABLE', '');
define('PSHOP_IMG_WIDTH', '90');
define('PSHOP_IMG_HEIGHT', '90');
define('PSHOP_COUPONS_ENABLE', '');
define('PSHOP_PDF_BUTTON_ENABLE', '');
define('PSHOP_SHOW_PRODUCTS_IN_CATEGORY', '');
define('PSHOP_SHOW_TOP_PAGENAV', '');
define('PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS', '1');

/* OrderByFields */
global $VM_BROWSE_ORDERBY_FIELDS;
$VM_BROWSE_ORDERBY_FIELDS = array(  );

/* Shipping Methods Definition */
global $PSHOP_SHIPPING_MODULES;
$PSHOP_SHIPPING_MODULES[0] = "no_shipping";
?>