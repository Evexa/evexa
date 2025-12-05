<?
use Sotbit\Reviews\Internals\BansTable;
use Bitrix\Iblock;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
require_once ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

global $APPLICATION;
if (!Loader::includeModule( 'iblock' ) || !Loader::includeModule( 'sotbit.reviews' ))
	die();
	
$POST_RIGHT = $APPLICATION->GetGroupRight( CSotbitReviews::iModuleID );
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$CSotbitReviews = new CSotbitReviews();
if (!$CSotbitReviews->getDemo())
	return false;

$aTabs = array(
		array(
				"DIV" => "edit1",
				"TAB" => GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_TAB" ),
				"ICON" => "main_user_edit",
				"TITLE" => GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_TAB_TITLE" ) 
		) 
);

$tabControl = new CAdminForm( "tabControl", $aTabs );
$ID = intval( $ID );

if ($ID > 0)
{
	$Result = BansTable::getById( $ID );
	$Result = $Result->fetch();
}

if (isset( $_REQUEST["ID_USER"] ) && $_REQUEST["ID_USER"])
	$Result["ID_USER"] = $_REQUEST["ID_USER"];
if (isset( $_REQUEST["IP"] ) && $_REQUEST["IP"])
	$Result["IP"] = $_REQUEST["IP"];
if (isset( $_REQUEST["DATE_TO"] ) && $_REQUEST["DATE_TO"])
	$Result["DATE_TO"] = $_REQUEST["DATE_TO"];
if (isset( $_REQUEST["REASON"] ) && $_REQUEST["REASON"])
	$Result["REASON"] = $_REQUEST["REASON"];
	
$message = null;

if ($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $POST_RIGHT == "W" && check_bitrix_sessid())
{
	if ($ID > 0)
	{
		$arFields = Array(
				"DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
				"DATE_TO" => new Type\DateTime( $DATE_TO ),
				"ID_USER" => $ID_USER,
				"IP" => $IP,
				"REASON" => $REASON,
				"ACTIVE" => ($ACTIVE != "Y" ? "N" : "Y"),
				"ID_MODERATOR" => \Bitrix\Main\Engine\CurrentUser::get()->getId()
				);
		
		
		$result = BansTable::update( $ID, $arFields );
		unset( $arFields );
		if (!$result->isSuccess())
		{
			$errors = $result->getErrorMessages();
			$res = false;
		}
		else
		{
			$res = true;
		}
	}
	else 
	{
		$arFields = Array(
				"DATE_CREATION" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
				"DATE_CHANGE" => new Type\DateTime( date( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' ),
				"DATE_TO" => new Type\DateTime( $DATE_TO ),
				"ID_USER" => $ID_USER,
				"IP" => $IP,
				"REASON" => $REASON,
				"ACTIVE" => ($ACTIVE != "Y" ? "N" : "Y"),
				"ID_MODERATOR" => \Bitrix\Main\Engine\CurrentUser::get()->getId()
				);
		
		$result = BansTable::add( $arFields );
		$ID = $result->getId();
	}
	unset( $result );
	if ($res)
	{
		if ($apply != "")
			LocalRedirect( "/bitrix/admin/sotbit.reviews_bans_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam() );
		else
			LocalRedirect( "/bitrix/admin/sotbit.reviews_bans_list.php?lang=" . LANG );
	}
}

$APPLICATION->SetTitle( ($ID > 0 ? GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_EDIT" ) . $ID : GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_ADD" )) );
require ($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
if ($CSotbitReviews->ReturnDemo() == 2)
	CAdminMessage::ShowMessage( array(
			"MESSAGE" => GetMessage( CSotbitReviews::iModuleID . "_MODULE_DEMO" ),
			'HTML' => true 
	) );
if ($CSotbitReviews->ReturnDemo() == 3)
	CAdminMessage::ShowMessage( array(
			"MESSAGE" => GetMessage( CSotbitReviews::iModuleID . "_MODULE_DEMO_END" ),
			'HTML' => true 
	) );

$aMenu = array(
		array(
				"TEXT" => GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_LIST" ),
				"TITLE" => GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_LIST_TITLE" ),
				"LINK" => "sotbit.reviews_bans_list.php?lang=" . LANG,
				"ICON" => "btn_list" 
		) 
);
if ($ID > 0)
{
	$aMenu[] = array(
			"TEXT" => GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_DEL" ),
			"TITLE" => GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_DEL_TITLE" ),
			"LINK" => "javascript:if(confirm('" . GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_DEL_CONF" ) . "'))window.location='sotbit.reviews_bans_list.php?ID=" . $ID . "&action=delete&lang=" . LANG ."&" . bitrix_sessid_get() . "';",
			"ICON" => "btn_delete" 
	);
}
$context = new CAdminContextMenu( $aMenu );
unset( $aMenu );
$context->Show();
unset( $context );
?>

<?
if ($_REQUEST["mess"] == "ok" && $ID > 0)
	CAdminMessage::ShowMessage( array(
			"MESSAGE" => GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_SAVED" ),
			"TYPE" => "OK" 
	) );
if (isset( $errors ) && !empty( $errors ))
{
	foreach ( $errors as $error )
	{
		CAdminMessage::ShowMessage( array(
				"MESSAGE" => $error 
		) );
	}
	unset( $error );
	unset( $errors );
}
?>
<?

// Moderator
if(isset($Result["ID_MODERATOR"]))
{
	$Moderators = CUser::GetByID( $Result["ID_MODERATOR"] );
	if ($arItem = $Moderators->Fetch())
	{
		$Moderator = '[' . $arItem['ID'] . '] ' . $arItem['LAST_NAME'] . ' ' . $arItem['NAME'];
	}
	unset( $Moderators );
	unset( $arItem );
}
// Start forms
$tabControl->Begin( array(
		"FORM_ACTION" => $APPLICATION->GetCurPage() 
) );
$tabControl->BeginNextFormTab(); // Condition tab

$tabControl->AddViewField( 'ID', GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_ID" ), $ID, false ); // ID

$tabControl->AddCheckBoxField( "ACTIVE", GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_ACT" ), false, "Y", ($Result['ACTIVE'] == "Y" || !isset( $Result['ACTIVE'] )) );
unset( $Result['ACTIVE'] );

$tabControl->BeginCustomField( "ID_USER", GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_ID_USER" ), false );
$arUser = \Sotbit\Reviews\Helper\Admin::getFieldUser($Result["ID_USER"]);
?>
<tr id="tr_ID_USER">
	<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
	<td width="60%">
        <input type="hidden" name="ID_USER" value="<?=$Result["ID_USER"]?>">
<?=$arUser ? $arUser['LINK'] : GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_ID_NO_AUTH_USER" )?>
</td>
</tr>
<?

$tabControl->EndCustomField( "ID_USER" );

$tabControl->AddEditField( "IP", GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_IP" ), false, array(
		"size" => 15,
		"maxlength" => 15 
), htmlspecialcharsbx( $Result['IP'] ) );

if(isset($Result['DATE_CREATION'])){
	$tabControl->AddViewField( 'DATE_CREATION', GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_DATE_CREATION" ), new Type\DateTime( $Result['DATE_CREATION'] ), false );
	unset( $Result['DATE_CREATION'] );
}
if(isset($Result['DATE_CHANGE'])){
	$tabControl->AddViewField( 'DATE_CHANGE', GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_DATE_CHANGE" ), new Type\DateTime( $Result['DATE_CHANGE'] ), false );
	unset( $Result['DATE_CHANGE'] );
}

$tabControl->BeginCustomField( "DATE_TO", GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_DATE_TO" ), false );

if(!isset($Result['DATE_TO']))
	$Result['DATE_TO']=date('d.m.Y H:i:s',strtotime('+1 year'));

?>
<tr id="tr_DATE_TO">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td><?echo CAdminCalendar::CalendarDate("DATE_TO", new Type\DateTime($Result['DATE_TO']), 19, true)?></td>
</tr>
<?
$tabControl->EndCustomField( "DATE_TO", '<input type="hidden" id="DATE_TO" name="DATE_TO" value="' . new Type\DateTime($Result['DATE_TO'])  . '">' );
unset( $Result['DATE_TO'] );

$tabControl->BeginCustomField( "REASON", GetMessage( CSotbitReviews::iModuleID . '_BAN_EDIT_REASON' ), false );
?>
<tr id="tr_REASON">
	<td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
	<td>
<?
$APPLICATION->IncludeComponent( "bitrix:main.post.form", "", Array(
        "FORM_ID" => "tabControl_form",
        "SHOW_MORE" => "Y",
        "PARSER" => array(
            "Bold", "Italic", "Underline", "Strike", "ForeColor",
            "FontList", "FontSizeList", "RemoveFormat", "Quote",
            "Code", "CreateLink",
            "Image", "UploadFile",
            "InputVideo",
            "Table", "Justify", "InsertOrderedList",
            "InsertUnorderedList",
            "Source", "MentionUser"
        ),
		'TEXT' => array(
				'SHOW' => 'Y',
				'VALUE' => $Result['REASON'],
				'NAME' => 'REASON'
		)
) );
?>

<style>
#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-top-bar-btn, #tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-top-bar-select,
	#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-fontsize-wrap {
	display: none;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-bold {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-italic {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-underline {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-strike {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-remove-format {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-top-bar-color {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-fontsize {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-ordered-list {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-unordered-list {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-align-left {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-quote {
	display: inline-block;
}

#tr_REASON .bxhtmled-top-bar-wrap .bxhtmled-button-align-right {
	display: inline-block;
}

#tr_REASON .bxhtmled-iframe-cnt {
	overflow: hidden !important;
}
</style>


	</td>
</tr>
<?
$tabControl->EndCustomField( "REASON" );
unset( $Result['REASON'] );
if(isset($Result["ID_MODERATOR"]))
{
	$tabControl->AddViewField( "ID_MODERATOR", GetMessage( CSotbitReviews::iModuleID . "_BAN_EDIT_MODERATED_BY" ), $Moderator, false );
	unset( $Moderator );
}
$tabControl->BeginCustomField( "HID", '', false );
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">

<?if($ID>0 && !$bCopy){?>
<input type="hidden" name="ID" value="<?=$ID?>">
<?}?>
<?

$tabControl->EndCustomField( "HID" );
$arButtonsParams = array(
		"disabled" => $readOnly,
		"back_url" => "/bitrix/admin/sotbit.reviews_bans_list.php?lang=" . LANG
);
$tabControl->Buttons( $arButtonsParams );
$tabControl->Show();
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>