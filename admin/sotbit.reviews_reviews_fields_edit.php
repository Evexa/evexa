<?

use Sotbit\Reviews\Internals\ReviewsfieldsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

if (!Loader::includeModule('sotbit.reviews'))
    die();


global $APPLICATION;
$POST_RIGHT = $APPLICATION->GetGroupRight(CSotbitReviews::iModuleID);

if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$CSotbitReviews = new CSotbitReviews();

if (!$CSotbitReviews->getDemo())
    return false;

$aTabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("REVIEWS_FIELDS_EDIT_TAB_CONDITION"),
        "ICON" => "main_user_edit",
        "TITLE" => Loc::getMessage("REVIEWS_FIELDS_EDIT_TAB_CONDITION_TITLE")
    )
);
$tabControl = new CAdminForm("tabControl", $aTabs);
$ID = intval($ID);
if ($ID > 0) {
    $Result = ReviewsfieldsTable::getById($ID);
    $Result = $Result->fetch();
}
if (isset($_REQUEST["SORT"]) && $_REQUEST["SORT"])
    $Result["SORT"] = $_REQUEST["SORT"];
if (isset($_REQUEST["NAME"]) && $_REQUEST["NAME"])
    $Result["NAME"] = $_REQUEST["NAME"];
if (isset($_REQUEST["TITLE"]) && $_REQUEST["TITLE"])
    $Result["TITLE"] = $_REQUEST["TITLE"];
if (isset($_REQUEST["TYPE"]) && $_REQUEST["TYPE"])
    $Result["TYPE"] = $_REQUEST["TYPE"];
if (isset($_REQUEST["SELECT_VALUE"]) && $_REQUEST["SELECT_VALUE"])
    $Result["SELECT_VALUE"] = $_REQUEST["SELECT_VALUE"];
if (isset($_REQUEST["ACTIVE"]) && $_REQUEST["ACTIVE"])
    $Result["ACTIVE"] = $_REQUEST["ACTIVE"];
$message = null;
if ($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && $POST_RIGHT == "W" && check_bitrix_sessid()) {
    $arFields = array(
        "SORT" => $SORT,
        "NAME" => $NAME,
        "TITLE" => $TITLE,
        "TYPE" => $TYPE,
        "SELECT_VALUE" => $SELECT_VALUE,
        "ACTIVE" => ($ACTIVE != "Y" ? "N" : "Y")
    );
    if ($ID > 0) {
        $result = ReviewsfieldsTable::update($ID, $arFields);
        unset($arFields);
        if (!$result->isSuccess()) {
            $errors = $result->getErrorMessages();
            $res = false;
        } else
            $res = true;
    } else {
       $isExistItem =  ReviewsfieldsTable::getList([
            'filter' => ['NAME' => $arFields['NAME']]
        ])->fetch();

       if($isExistItem){
           $errors[] = Loc::getMessage('REVIEWS_ERROR_CODE');
       }else{
           $result = ReviewsfieldsTable::add($arFields);
           if ($result->isSuccess()) {
               $ID = $result->getId();
               $res = true;
           } else {
               $errors = $result->getErrorMessages();
               $res = false;
           }
       }
    }
    unset($result);
    if ($res) {
        if ($apply != "")
            LocalRedirect("/bitrix/admin/sotbit.reviews_reviews_fields_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
        else
            LocalRedirect("/bitrix/admin/sotbit.reviews_reviews_fields_list.php?lang=" . LANG);
    }
}
$APPLICATION->SetTitle(($ID > 0 ? Loc::getMessage("REVIEWS_FIELDS_EDIT_EDIT") . $ID : Loc::getMessage("REVIEWS_FIELDS_EDIT_ADD")));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
if ($CSotbitReviews->ReturnDemo() == 2)
    CAdminMessage::ShowMessage(array(
        "MESSAGE" => Loc::getMessage("MODULE_DEMO"),
        'HTML' => true
    ));
if ($CSotbitReviews->ReturnDemo() == 3)
    CAdminMessage::ShowMessage(array(
        "MESSAGE" => Loc::getMessage("MODULE_DEMO_END"),
        'HTML' => true
    ));
$aMenu = array(
    array(
        "TEXT" => Loc::getMessage("REVIEWS_FIELDS_EDIT_LIST"),
        "TITLE" => Loc::getMessage("REVIEWS_FIELDS_EDIT_LIST_TITLE"),
        "LINK" => "sotbit.reviews_reviews_fields_list.php?lang=" . LANG,
        "ICON" => "btn_list"
    )
);
if ($ID > 0) {
    $aMenu[] = array(
        "SEPARATOR" => "Y"
    );
    $aMenu[] = array(
        "TEXT" => Loc::getMessage("REVIEWS_FIELDS_EDIT_ADD"),
        "TITLE" => Loc::getMessage("REVIEWS_FIELDS_EDIT_ADD_TITLE"),
        "LINK" => "sotbit.reviews_reviews_fields_edit.php?lang=" . LANG,
        "ICON" => "btn_new"
    );
    $aMenu[] = array(
        "TEXT" => Loc::getMessage("REVIEWS_FIELDS_EDIT_DEL"),
        "TITLE" => Loc::getMessage("REVIEWS_FIELDS_EDIT_DEL_TITLE"),
        "LINK" => "javascript:if(confirm('" . Loc::getMessage("REVIEWS_FIELDS_EDIT_DEL_CONF") . "'))window.location='sotbit.reviews_reviews_fields_list.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
        "ICON" => "btn_delete"
    );
}
$context = new CAdminContextMenu($aMenu);
unset($aMenu);
$context->Show();
unset($context);
?>

<?
if ($_REQUEST["mess"] == "ok" && $ID > 0)
    CAdminMessage::ShowMessage(array(
        "MESSAGE" => Loc::getMessage("REVIEWS_FIELDS_EDIT_SAVED"),
        "TYPE" => "OK"
    ));

if (isset($errors) && !empty($errors)) {
    foreach ($errors as $error) {
        CAdminMessage::ShowMessage(array(
            "MESSAGE" => $error
        ));
    }
    unset($error);
    unset($errors);
}
?>
<?

$arTypes["REFERENCE_ID"] = array(
    'textbox',
    'number',
    'select',
    'file'
);
$arTypes["REFERENCE"] = array(
    Loc::getMessage('REVIEWS_FIELDS_EDIT_TYPE_TEXTBOX'),
    Loc::getMessage('REVIEWS_FIELDS_EDIT_TYPE_NUMBER'),
    Loc::getMessage('REVIEWS_FIELDS_EDIT_TYPE_SELECT'),
    Loc::getMessage('REVIEWS_FIELDS_EDIT_TYPE_FILE'),
);
if (!isset($Result['SORT']) || empty($Result['SORT']))
    $Result['SORT'] = 100;
$tabControl->Begin(array(
    "FORM_ACTION" => $APPLICATION->GetCurPage()
));
$tabControl->BeginNextFormTab();
$tabControl->AddViewField('ID', Loc::getMessage("REVIEWS_FIELDS_EDIT_ID"), $ID, false); // ID
$tabControl->AddCheckBoxField("ACTIVE", Loc::getMessage("REVIEWS_FIELDS_EDIT_ACT"), false, "Y", ($Result['ACTIVE'] == "Y" || !isset($Result['ACTIVE'])));
unset($Result['ACTIVE']);
$tabControl->AddEditField('SORT', Loc::getMessage("REVIEWS_FIELDS_EDIT_SORT"), true, array(), htmlspecialcharsbx($Result['SORT']));
unset($Result['SORT']);
$tabControl->AddEditField('NAME', Loc::getMessage("REVIEWS_FIELDS_EDIT_NAME"), true, array(), htmlspecialcharsbx($Result['NAME']));
unset($Result['NAME']);
$tabControl->AddEditField('TITLE', Loc::getMessage("REVIEWS_FIELDS_EDIT_TITLE"), true, array(), htmlspecialcharsbx($Result['TITLE']));
unset($Result['TITLE']);
$tabControl->BeginCustomField("TYPE", Loc::getMessage("REVIEWS_FIELDS_EDIT_TYPE"), false);
?>
    <tr id="tr_TYPE">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td>
            <?= SelectBoxFromArray('TYPE', $arTypes, $Result['TYPE'], '', 'style="min-width:350px"', true, ''); ?>
        </td>
    </tr>
<?
$tabControl->EndCustomField("TYPE");
unset($Result['TYPE']);

$tabControl->AddTextField('SELECT_VALUE', Loc::getMessage("REVIEWS_SELECT_VALUE"), $Result['SELECT_VALUE'], false);
unset($Result['SELECT_VALUE']);
$tabControl->BeginCustomField("HID", '', false);

?>
<? echo bitrix_sessid_post(); ?>
    <input type="hidden" name="lang" value="<?= LANG ?>">
<? if ($ID > 0 && !$bCopy): ?>
    <input type="hidden" name="ID" value="<?= $ID ?>">
<? endif; ?>
<?

$tabControl->EndCustomField("HID");
$arButtonsParams = array(
    "disabled" => $readOnly,
    "back_url" => "/bitrix/admin/sotbit.reviews_reviews_fields_list.php?lang=" . LANG
);
$tabControl->Buttons($arButtonsParams);
$tabControl->Show();
?>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>