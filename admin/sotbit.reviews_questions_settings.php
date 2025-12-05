<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Helper\Admin;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

const MODULE_NAME = "sotbit.reviews";
global $APPLICATION;

IncludeModuleLangFile(__FILE__);

if ($APPLICATION->GetGroupRight("main") < "R") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if (!Loader::includeModule(MODULE_NAME)) {
    return;
}

\Bitrix\Main\UI\Extension::load("ui.hint");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$request = Application::getInstance()->getContext()->getRequest();
$site = $request->get('site');
$arOptions = OptionReviews::getConfigsQuestions($site);

if (count(OptionReviews::getDefaultConfigs()) !== count(OptionReviews::getConfigs($site))) {
    OptionReviews::setDefault([$site]);
}
$arSite = OptionReviews::getSites();
$arIds = Admin::getSelectElement();
$mailNotice = Admin::getMailTemplate('SOTBIT_REVIEWS_ADD_NOTICE_MAILING_EVENT_SEND');
$mailNoticeCoupon = Admin::getMailTemplate('SOTBIT_REVIEWS_ADD_MAILING_EVENT_SEND');
$arVariantFile = Admin::getVariantFile();

if (Loader::includeModule('catalog') && Loader::includeModule('sale')) {
    $arCoupon = Admin::getCoupon();
    $orderStatus = Admin::getOrderStatus();
    $arDisc = Admin::getDiscount();
    $mailNote = Admin::getMailTemplate('SOTBIT_REVIEWS_ADD_MAILING_EVENT_SEND');
    $currencies = Admin::getCurrency();
    $groups = Admin::getUserGroup();
}

$isActive = $arOptions["ENABLE_QUESTIONS"] == 'Y' ?: false;

// SAVE
$requestValues = $request->getValues();

if ($request->isPost() && $request->get('save') <> '') {
    if ($isActive) {
        foreach ($arOptions as $key => $value) {
            if (in_array($key, array_keys($requestValues)) && !empty($requestValues[$key])) {
                $arOptions[$key] = OptionReviews::setSiteParam($key, $requestValues[$key], $site);
            } else {
                if ($arOptions[$key] == 'Y') {
                    $arOptions[$key] = OptionReviews::setSiteParam($key, $requestValues[$key] ?: 'N', $site);
                } elseif ($key == 'QUESTIONS_REPEAT') {
                    $arOptions[$key] = OptionReviews::setSiteParam($key, $requestValues[$key] ?: 0, $site);
                }
            }
        }
    }

    if ($requestValues['ENABLE_QUESTIONS'] == 'Y') {
        $arOptions['ENABLE_QUESTIONS'] = OptionReviews::setSiteParam('ENABLE_QUESTIONS', $requestValues['ENABLE_QUESTIONS'], $site);
        $isActive = true;
    } else {
        $arOptions['ENABLE_QUESTIONS'] = OptionReviews::setSiteParam('ENABLE_QUESTIONS', 'N', $site);
        $isActive = false;
    }
}

// Tabs list
$arTabs = [
    ['DIV' => 'edit1', 'TAB' => Loc::getMessage('edit1'), 'ICON' => '', 'TITLE' => Loc::getMessage('edit1'), 'SORT' => '10'],
    ['DIV' => 'edit2', 'TAB' => Loc::getMessage('edit2'), 'ICON' => '', 'TITLE' => Loc::getMessage('edit2'), 'SORT' => '20'],
    ['DIV' => 'edit3', 'TAB' => Loc::getMessage('edit3'), 'ICON' => '', 'TITLE' => Loc::getMessage('edit3'), 'SORT' => '30']
];

$formName = 'sotbit_question_discussion';
$tabControl = new CAdminForm($formName, $arTabs);
$tabControl->Begin();

// ------------------------------- Tab basic settings ------------------------

$tabControl->BeginNextFormTab();

// ------------------------------- Section basic settings --------------------

$tabControl->AddSection('GROUP_SETTINGS', Loc::getMessage('GROUP_SETTINGS'));

$tabControl->AddCheckBoxField("ENABLE_QUESTIONS", Loc::getMessage('ENABLE'), false, 'Y',
    $arOptions["ENABLE_QUESTIONS"] == 'N' ? '' : $arOptions["ENABLE_QUESTIONS"], []);

$tabControl->AddCheckBoxField('QUESTIONS_MODERATION', Loc::getMessage('MODERATION_QUESTIONS'), false, 'Y',
    $arOptions["QUESTIONS_MODERATION"] == 'N' ? '' : $arOptions["QUESTIONS_MODERATION"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->AddDropDownField('QUESTIONS_ID_ELEMENT', Loc::getMessage('QUESTIONS_ID_ELEMENT'), false,
    $arIds, $arOptions['QUESTIONS_ID_ELEMENT'], ($isActive ? [''] : [' disabled']));

$tabControl->AddCheckBoxField('QUESTIONS_DELETE', Loc::getMessage('QUESTIONS_DELETE'), false, 'Y',
    $arOptions["QUESTIONS_DELETE"] == 'N' ? '' : $arOptions["QUESTIONS_DELETE"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->AddCheckBoxField('QUESTIONS_SCHEMA_ORG', Loc::getMessage('QUESTIONS_SCHEMA_ORG'), false, 'Y',
    $arOptions["QUESTIONS_SCHEMA_ORG"] == 'N' ? '' : $arOptions["QUESTIONS_SCHEMA_ORG"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->BeginCustomField("QUESTIONS_NOTICE_MAIL", '', false);
?>
<tr id="tr_REVIEWS_NOTICE_MAIL">
    <td width="40%" class="adm-detail-content-cell-l">
        <?= Loc::getMessage('QUESTIONS_MAIL_LINK') ?>
    </td>
    <td class="adm-detail-content-cell-r">
        <?= $mailNotice ?>
    </td>
</tr>
<?php
$tabControl->EndCustomField("QUESTIONS_NOTICE_MAIL");

// ------------------------------- Section user interaction --------------------

$tabControl->AddSection('GROUP_USER', Loc::getMessage('GROUP_USER'));

$tabControl->BeginCustomField("QUESTIONS_NO_USER_IMAGE", '', false);
echo Admin::getFileField('QUESTIONS_NO_USER_IMAGE', Loc::getMessage('NO_USER_IMAGE'), $arOptions['QUESTIONS_NO_USER_IMAGE'], $isActive ? '' : 'N');
$tabControl->EndCustomField("QUESTIONS_NO_USER_IMAGE");

$tabControl->AddCheckBoxField('QUESTIONS_ANONYMOUS', Loc::getMessage('QUESTIONS_ANONYMOUS'), false, 'Y',
    $arOptions["QUESTIONS_ANONYMOUS"] == 'N' ? '' : $arOptions["QUESTIONS_ANONYMOUS"], [0 => ($isActive ? '' : 'disabled')]);

if (Loader::includeModule('catalog') && Loader::includeModule('sale')) {
    $tabControl->AddCheckBoxField('QUESTIONS_IF_BUY', Loc::getMessage('QUESTIONS_IF_BUY'), false, 'Y',
        $arOptions["QUESTIONS_IF_BUY"] == 'N' ? '' : $arOptions["QUESTIONS_IF_BUY"], [0 => ($isActive ? '' : 'disabled')]);

    $tabControl->BeginCustomField("QUESTIONS_ORDER_STATUS", '', false);
    echo Admin::getMultiSelectField("QUESTIONS_ORDER_STATUS", Loc::getMessage('ORDER_STATUS_QUESTIONS'), $orderStatus, $arOptions['QUESTIONS_ORDER_STATUS'], ($isActive ? '' : 'disabled'), Loc::getMessage('ORDER_STATUS_NOTES_REVIEWS'));
    $tabControl->EndCustomField("QUESTIONS_ORDER_STATUS");

    $tabControl->AddCheckBoxField('QUESTIONS_PAYED', Loc::getMessage('PAYED_QUESTIONS'), false, 'Y',
        $arOptions["QUESTIONS_PAYED"] == 'N' ? '' : $arOptions["QUESTIONS_PAYED"], [0 => ($isActive ? '' : 'disabled')]);
}

$tabControl->AddCheckBoxField('QUESTIONS_EDIT', Loc::getMessage('QUESTIONS_EDIT'), false, 'Y',
    $arOptions["QUESTIONS_EDIT"] == 'N' ? '' : $arOptions["QUESTIONS_EDIT"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->BeginCustomField("QUESTIONS_REPEAT", '', false);
echo Admin::getNumberField("QUESTIONS_REPEAT", Loc::getMessage('QUESTIONS_REPEAT'), $arOptions["QUESTIONS_REPEAT"], '-1', Loc::getMessage('QUESTIONS_REPEAT_NOTE'), ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("QUESTIONS_REPEAT");

// ------------------------------- Tab Discounts and invoices --------------------

$tabControl->BeginNextFormTab();

// ------------------------------- Section discounts  ----------------------------

$tabControl->AddSection('GROUP_COUPONS', Loc::getMessage('GROUP_COUPONS'));

$tabControl->BeginCustomField("QUESTIONS_ADD_COUPON", '', false);
?>
<tr id="tr_QUESTIONS_ADD_COUPON">
    <td width="40%" class="adm-detail-content-cell-l">
        <?=Loc::getMessage('ADD_COUPON')?>
    </td>
    <td class="adm-detail-content-cell-r">
        <input type="checkbox"
               name="QUESTIONS_ADD_COUPON"
               value="Y"
        <?= $arOptions["QUESTIONS_ADD_COUPON"] == 'N' ? '' : 'checked'?>
        <?= $isActive ? '' : 'disabled'?>
        </label>
        <span class="ui-hint" data-hint="<?=Loc::getMessage('ADD_COUPON_HINT')?>" data-hint-html></span>
    </td>
</tr>
<?php
$tabControl->EndCustomField("QUESTIONS_ADD_COUPON");

$tabControl->AddCheckBoxField('QUESTIONS_SEND_MAIL', Loc::getMessage('SEND_MAIL'), false, 'Y',
    $arOptions["QUESTIONS_SEND_MAIL"] == 'N' ? '' : $arOptions["QUESTIONS_SEND_MAIL"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->BeginCustomField("QUESTIONS_MAIL_BILL_LINK", '', false);
?>
<tr id="tr_QUESTIONS_NOTICE_MAIL">
    <td width="40%" class="adm-detail-content-cell-l">
        <?= Loc::getMessage('QUESTIONS_MAIL_BILL_LINK') ?>
    </td>
    <td class="adm-detail-content-cell-r">
        <?= $mailNoticeCoupon ?>
    </td>
</tr>
<?php
$tabControl->EndCustomField("QUESTIONS_MAIL_BILL_LINK");

$tabControl->AddDropDownField('QUESTIONS_ID_DISCOUNT', Loc::getMessage('ID_DISCOUNT'), false,
    $arDisc, $arOptions['QUESTIONS_ID_DISCOUNT'], ($isActive ? [''] : [' disabled']));

$tabControl->AddDropDownField('QUESTIONS_COUPON_TYPE', Loc::getMessage('COUPON_TYPE'), false,
    $arCoupon['TYPE'], $arOptions['QUESTIONS_COUPON_TYPE'], ($isActive ? [''] : [' disabled']));

// ------------------------------- Section bill  ----------------------------

$tabControl->AddSection('GROUP_BILLS', Loc::getMessage('GROUP_BILLS'));

$tabControl->BeginCustomField("QUESTIONS_BILL_ADD_REVIEW", '', false);
echo Admin::getNumberField("QUESTIONS_BILL_ADD_REVIEW", Loc::getMessage('QUESTIONS_BILL_ADD_QUESTIONS'), $arOptions["QUESTIONS_BILL_ADD_REVIEW"], 0, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("QUESTIONS_BILL_ADD_REVIEW");

$tabControl->BeginCustomField("QUESTIONS_BILL_LIKE_REVIEW", '', false);
echo Admin::getNumberField("QUESTIONS_BILL_LIKE_REVIEW", Loc::getMessage('QUESTIONS_BILL_LIKE_QUESTIONS'), $arOptions["QUESTIONS_BILL_LIKE_REVIEW"], 0, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("QUESTIONS_BILL_LIKE_REVIEW");

$tabControl->BeginCustomField("QUESTIONS_BILL_DISLIKE_REVIEW", '', false);
echo Admin::getNumberField("QUESTIONS_BILL_DISLIKE_REVIEW", Loc::getMessage('QUESTIONS_BILL_DISLIKE_QUESTIONS'), $arOptions["QUESTIONS_BILL_DISLIKE_REVIEW"], 0, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("QUESTIONS_BILL_DISLIKE_REVIEW");

$tabControl->AddDropDownField('QUESTIONS_BILL_CURRENCY_REVIEW', Loc::getMessage('QUESTIONS_BILL_CURRENCY_QUESTIONS'), false,
    $currencies, $arOptions['QUESTIONS_BILL_CURRENCY_REVIEW'], ($isActive ? [''] : [' disabled']));

$tabControl->BeginCustomField("QUESTIONS_BILL_GROUP", '', false);
echo Admin::getMultiSelectField("QUESTIONS_BILL_GROUP", Loc::getMessage('QUESTIONS_BILL_GROUP'), $groups, $arOptions['QUESTIONS_BILL_GROUP'], ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("QUESTIONS_BILL_GROUP");

$RIGHT = $APPLICATION->GetGroupRight(MODULE_NAME);
if ($RIGHT != "D") {
    if ((CSotbitReviews::ReturnDemo() == 2)) {
        ?>
        <div class="adm-info-message-wrap adm-info-message-red">
            <div class="adm-info-message">
                <div class="adm-info-message-title"><?= Loc::getMessage("sotbit_ms_demo") ?></div>
                <div class="adm-info-message-icon"></div>
            </div>
        </div>
        <?php
    }
}

$tabControl->arParams["FORM_ACTION"] = $APPLICATION->GetCurPageParam();
$arButtonsParams = array(
    'disabled' => false,
    'btnApply' => false
);

$tabControl->Buttons($arButtonsParams);

$tabControl->SetShowSettings(false);
$tabControl->Show();

$APPLICATION->SetTitle(Loc::getMessage('TITLE'));
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>
