<?
use Bitrix\Main\Loader;
use Sotbit\Reviews\Bill\Coupon;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if(!Loader::includeModule( 'sotbit.reviews' ))
	die();

if($REQUEST_METHOD=="POST")
{ 
	foreach( $_POST as $key => $val )
		$arFields[$key] = "${$key}";
	$SITE_ID = $_POST['SITE_ID'];
	if(COption::GetOptionString( CSotbitReviews::iModuleID, "REVIEWS_ADD_COUPON_".$SITE_ID, "" )=='Y'&&COption::GetOptionString( CSotbitReviews::iModuleID, "REVIEWS_MODERATION_".$SITE_ID, "" )=='Y')
        Coupon::addCoupon( $arFields );
}
?>