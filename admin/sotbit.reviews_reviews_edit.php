<?
use Sotbit\Reviews\Internals\ReviewsTable;
use Sotbit\Reviews\Internals\ReviewsfieldsTable;
use Sotbit\Reviews\Model\Reviews;
use Sotbit\Reviews\Helper;
use Sotbit\Reviews\Controller\Ban;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Type;


require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

global $APPLICATION;

if (!Loader::includeModule('iblock') || !Loader::includeModule('sotbit.reviews')) {
    die();
}

$POST_RIGHT = $APPLICATION->GetGroupRight(CSotbitReviews::iModuleID);

if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

$CSotbitReviews = new CSotbitReviews();
if (!$CSotbitReviews->getDemo()) {
    return false;
}

$aTabs[] = [
    "DIV" => "edit1",
    "TAB" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_TAB_CONDITION"),
    "ICON" => "main_user_edit",
    "TITLE" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_TAB_CONDITION_TITLE")
];

$tabControl = new CAdminForm("tabControl", $aTabs);
$request = Main\Application::getInstance()->getContext()->getRequest();

$ID = intval($request->get('ID'));

if ($ID > 0) {
    $result = ReviewsTable::getById($ID);
    $result = $result->fetch();
}

$arElement = Helper\Admin::getLinkElement($result['ID_ELEMENT']);
$message = null;
if ($request->isPost()) {
    if (($request->get('apply') || $request->get('save')) && ($POST_RIGHT == "W") && check_bitrix_sessid()) {
        if ($ID > 0) {

            $el_res = CIBlockElement::GetByID($result['ID_ELEMENT']);
            if ($el_arr = $el_res->GetNext()) {
                $result['SITE'] = $el_arr['LID'];
            }

            $fields = Helper\Admin::getFields($request, true);
            $resultUpdate = Reviews::update($ID, $fields, $result);

            if (!$resultUpdate->isSuccess()) {
                $errors = $resultUpdate->getErrorMessages();
            } else {
                if ($request->get('apply') != '')
                    LocalRedirect("/bitrix/admin/sotbit.reviews_reviews_edit.php?ID=" . $ID . "&mess=ok&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
                else
                    LocalRedirect("/bitrix/admin/sotbit.reviews_reviews_list.php?lang=" . LANG);
            }
        }
    }elseif($request->get('ban') != ''){

        $control = new Ban();
        $resultBan = $control->addBanAction($ID, ReviewsTable::class);

        if (!$resultBan->isSuccess()) {
            $errors = $resultBan->getErrorMessages();
        } else {
            LocalRedirect("/bitrix/admin/sotbit.reviews_reviews_edit.php?ID=" . $ID . "&mess=okBan&lang=" . LANG . "&" . $tabControl->ActiveTabParam());
        }
    }
}

$APPLICATION->SetTitle(($ID > 0 ? GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_EDIT") . $ID : GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_ADD")));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

if ($CSotbitReviews->ReturnDemo() == 2) {
    CAdminMessage::ShowMessage(array(
        "MESSAGE" => GetMessage(CSotbitReviews::iModuleID . "_MODULE_DEMO"),
        'HTML' => true
    ));
}

if ($CSotbitReviews->ReturnDemo() == 3) {
    CAdminMessage::ShowMessage(array(
        "MESSAGE" => GetMessage(CSotbitReviews::iModuleID . "_MODULE_DEMO_END"),
        'HTML' => true
    ));
}

$aMenu[] = [
    "TEXT" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_LIST"),
    "TITLE" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_LIST_TITLE"),
    "LINK" => "sotbit.reviews_reviews_list.php?lang=" . LANG,
    "ICON" => "btn_list"
];

if ($ID > 0) {
    $aMenu[] = [
        "TEXT" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_DEL"),
        "TITLE" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_DEL_TITLE"),
        "LINK" => "javascript:if(confirm('" . GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_DEL_CONF") . "'))window.location='sotbit.reviews_reviews_list.php?ID=" . $ID . "&action=delete&lang=" . LANG . "&" . bitrix_sessid_get() . "';",
        "ICON" => "btn_delete"
    ];
}

$context = new CAdminContextMenu($aMenu);
$context->Show();
unset($context, $aMenu);

if ($request->get('mess') == "ok" && $ID > 0) {
    CAdminMessage::ShowMessage([
        "MESSAGE" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_SAVED"),
        "TYPE" => "OK"
    ]);
}

if ($request->get('mess') == "okBan") {
    CAdminMessage::ShowMessage([
        "MESSAGE" => GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_ADD_BAN"),
        "TYPE" => "OK"
    ]);
}

if (isset($errors) && !empty($errors)) {
    foreach ($errors as $error) {
        CAdminMessage::ShowMessage([
            "MESSAGE" => $error
        ]);
    }
    unset($error, $errors);
}

$ADD_FIELDS = unserialize($result['ADD_FIELDS'], ['allowed_classes' => false]);
$arUser = Helper\Admin::getFieldUser($result["ID_USER"]);
$arUserModerator = Helper\Admin::getFieldUser($result["MODERATED_BY"]);
$Moderator = $arUserModerator['NAME'];
$Moderators = CUser::GetByID($result["MODERATED_BY"]);

$tabControl->Begin(array(
    "FORM_ACTION" => $APPLICATION->GetCurPage()
));
$tabControl->BeginNextFormTab();
$tabControl->AddViewField('ID', GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_ID"), $ID, false); // ID
$tabControl->AddCheckBoxField("ACTIVE", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_ACT"), false, "Y", ($result['ACTIVE'] == "Y" || !isset($result['ACTIVE']))); // ??????????
unset($result['ACTIVE']);

if (Helper\OptionReviews::getConfig( "REVIEWS_MODERATION" , $arElement['LID']) == 'Y') {
    $tabControl->AddCheckBoxField("MODERATED", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_MODERATED"), false, "Y", ($result['MODERATED'] == "Y" || !isset($result['MODERATED']))); // ?????? ?????????
    unset($result['MODERATED']);
} else {
    $tabControl->BeginCustomField("MODERATED", "", false);
    ?>
    <input type="hidden" name="MODERATED" value="<?= $result['MODERATED'] ?>">
    <?
    $tabControl->EndCustomField("MODERATED");
}
$tabControl->AddViewField("ID_ELEMENT", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_ID_ELEMENT"), $arElement['NAME'], false);
$tabControl->BeginCustomField("ID_ELEMENT_LINK", "", false);
?>
<tr id="tr_TEXT">
    <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
    <td><a href="<?= $arElement['DETAIL_PAGE_URL'] ?>"
           target="_blank"><?= GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_LINK") ?></a>
        <br>
        <a href="<?= $arElement['DETAIL_PAGE_ADMIN_URL'] ?>"
           target="_blank"><?= GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_LINK_ADMIN") ?></a>
        <input name="ID_ELEMENT" type="hidden" value="<?= $arElement['ID'] ?>"></td>
</tr>
<?
$tabControl->EndCustomField("ID_ELEMENT_LINK");
unset($Product);
unset($IdProduct);
$tabControl->AddViewField("XML_ID_ELEMENT", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_XML_ID_ELEMENT"), $result['XML_ID_ELEMENT'], false);
$tabControl->AddViewField("ID_USER", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_ID_USER"), $arUser ? $arUser['LINK'] : GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ELEMENT_EDIT_USER_NO_AUTH'), false); // ID ????????????
$tabControl->BeginCustomField("ID_USER_HID", GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ELEMENT_EDIT_RATING'), false);
?>
<input type="hidden" name="ID_USER" value="<?= $arUser['ID'] ?>">
<?
$tabControl->EndCustomField("ID_USER_HID");
unset($arUser['NAME']);
$tabControl->AddViewField("IP_USER", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_IP_USER"), $result['IP_USER'], false);
$tabControl->BeginCustomField("RATING", GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ELEMENT_EDIT_RATING'), false);
?>
<tr id="tr_ANSWER">
    <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
    <td>
        <?
        for ($i = 1; $i <= $result['RATING']; ++$i)
            echo '&#9733;';
        ?>
        <br> <input min="1" name="RATING" type="number"
                    value="<?= $result['RATING'] ?>">
    </td>
</tr>
<?
$tabControl->EndCustomField("RATING");
unset($result['RATING']);
$tabControl->BeginCustomField("DATE_CREATION", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_DATE_CREATION"), false);
?>
<tr id="tr_ADATE_CREATION">
    <td width="40%"><? echo $tabControl->GetCustomLabelHTML() ?></td>
    <td><? echo CAdminCalendar::CalendarDate("DATE_CREATION", new Type\DateTime($result['DATE_CREATION']), 19, true) ?></td>
</tr>
<?
$tabControl->EndCustomField("DATE_CREATION", '<input type="hidden" id="DATE_CREATION" name="DATE_CREATION" value="' . new Type\DateTime($result['DATE_CREATION']) . '">');
unset($result['DATE_CREATION']);
$tabControl->AddViewField('DATE_CHANGE', GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_DATE_CHANGE"), new Type\DateTime($result['DATE_CHANGE']), false); // ???? ?????????
unset($result['DATE_CHANGE']);

$tabControl->AddTextField('TEXT', GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ELEMENT_EDIT_TEXT'), $result['TEXT'] ,["cols" => 100, 'rows' => 10]);
$tabControl->AddTextField('ANSWER', GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ELEMENT_EDIT_ANSWER'), $result['ANSWER'] ,["cols" => 100, 'rows' => 10]);

$tabControl->AddEditField('LIKES', GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_LIKES"), true, array(), htmlspecialcharsbx($result['LIKES'])); // Likes
unset($result['LIKES']);
$tabControl->AddEditField('DISLIKES', GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_DISLIKES"), true, array(), htmlspecialcharsbx($result['DISLIKES'])); // Dislikes
unset($result['DISLIKES']);

if (Helper\OptionReviews::getConfig("REVIEWS_MODERATION" , $arElement['LID']) == 'Y') {
    $tabControl->AddViewField("MODERATED_BY", GetMessage(CSotbitReviews::iModuleID . "_REVIEWS_ELEMENT_EDIT_MODERATED_BY"), $Moderator, false);
}
$tabControl->BeginCustomField("SEPARATOR", GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ELEMENT_EDIT_SEPARATOR'), false);
?>
<tr id="tr_SEPARATOR" class="heading">
    <td colspan="2"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
</tr>
<?
$tabControl->EndCustomField("SEPARATOR");
$rsData = ReviewsfieldsTable::getList(array(
    'select' => array(
        'NAME',
        'TYPE',
        'TITLE',
        'SELECT_VALUE'
    ),
    'filter' => array(
        'ACTIVE' => 'Y'
    ),
    'order' => array(
        'SORT' => 'asc'
    )
));
while ($arRes = $rsData->fetch()) {
    if (isset($arRes['TITLE']) && !empty($arRes['TITLE']))
        $Translate = $arRes['TITLE'];
    else
        $Translate = GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ADD_FIELD_' . $arRes['NAME']);
    if ($arRes['TYPE'] == 'textbox') {
        $tabControl->BeginCustomField($arRes['NAME'], $Translate, false);
        ?>
        <tr class="tr_textbox" id="tr_<?= $arRes['NAME'] ?>">
            <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
            <td>

                <?
                $APPLICATION->IncludeComponent("bitrix:main.post.form", "", array(
                    'BUTTONS' => array(),
                    'PARSER' => array(),
                    'PIN_EDITOR_PANEL' => 'N',
                    'TEXT' => array(
                        'SHOW' => 'Y',
                        'VALUE' => $ADD_FIELDS[$arRes['NAME']],
                        'NAME' => "ADD_FIELDS_" . $arRes['NAME']
                    )
                ));
                ?>
            </td>
        </tr>
        <?
        $tabControl->EndCustomField($arRes['NAME']);
        unset($result['TYPE']);
        unset($arRes);
    }elseif($arRes['TYPE'] == 'number'){
        $tabControl->BeginCustomField("ADD_FIELDS_" . $arRes['NAME'], '', false);
        echo Helper\Admin::getNumberField("ADD_FIELDS_" . $arRes['NAME'], $arRes['TITLE'], $ADD_FIELDS[$arRes['NAME']]);
        $tabControl->EndCustomField("ADD_FIELDS_" . $arRes['NAME']);
    }elseif($arRes['TYPE'] == 'select'){
        if(!empty($arRes['SELECT_VALUE'])){
            if (LANG_CHARSET == 'windows-1251') {
                $text = iconv("WINDOWS-1251", "UTF-8", $arRes['SELECT_VALUE']);
            }
            $decodeString = json_decode($text);
            if(LANG_CHARSET == 'windows-1251'){
                foreach ($decodeString as $key =>  $item){
                    $resJson[$key] = iconv("UTF-8", "WINDOWS-1251", $item);
                }
            }

            $arRes['SELECT_VALUE'] = $resJson;
        }

        $tabControl->AddDropDownField("ADD_FIELDS_" . $arRes['NAME'], $arRes['TITLE'], false,
            $arRes['SELECT_VALUE'], $ADD_FIELDS[$arRes['NAME']], '');

    }elseif($arRes['TYPE'] == 'file'){
        $tabControl->BeginCustomField("ADD_FIELDS_" . $arRes['NAME'], '', false);
        echo Helper\Admin::getFileField("ADD_FIELDS_" . $arRes['NAME'], $arRes['TITLE'], $ADD_FIELDS[$arRes['NAME']]);
        $tabControl->EndCustomField("ADD_FIELDS_" . $arRes['NAME']);
    }
}
$tabControl->BeginCustomField("HID", '', false);
?>
<? echo bitrix_sessid_post(); ?>
<input type="hidden" name="IFMODERATED" value="<?= $Moderator ?>">
<input type="hidden" name="lang" value="<?= LANG ?>">
<? if ($ID > 0 && !$bCopy) { ?>
    <input type="hidden" name="ID" value="<?= $ID ?>">
<? } ?>
<?
$tabControl->EndCustomField("HID");
$arButtonsParams = array(
    "disabled" => $readOnly,
    "back_url" => "/bitrix/admin/sotbit.reviews_reviews_list.php?lang=" . LANG
);

$htmlBtnBan = '<input class="mybutton" type="submit" name="ban" value="'.GetMessage(CSotbitReviews::iModuleID . '_REVIEWS_ELEMENT_EDIT_BTN_BAN').'"/>&nbsp;';
$tabControl->Buttons($arButtonsParams, $htmlBtnBan);
$tabControl->Show();

?>
<style>
    .mybutton {
        color: var(--ui-counter-current-bg-color) !important;
    }
    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-top-bar-btn, .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-top-bar-select, .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-fontsize-wrap {
        display: none;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-bold {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-italic {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-underline {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-strike {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-remove-format {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-top-bar-color {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-fontsize {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-ordered-list {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-unordered-list {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-align-left {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-quote {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-top-bar-wrap .bxhtmled-button-align-right {
        display: inline-block;
    }

    .tr_textbox .bxhtmled-iframe-cnt {
        overflow: hidden !important;
    }
</style>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
