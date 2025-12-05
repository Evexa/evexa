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
$arOptions = OptionReviews::getConfigsReviews($site);
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

// to support old functionality
foreach ($arOptions as $key => $value) {
    foreach ($arSite as $siteId => $name) {
        if (strripos($key, ('_' . $siteId))) {
            unset($arOptions[$key]);
        }
    }
}

$isActive = $arOptions["ENABLE_REVIEWS"] == 'Y' ?: false;

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
                } elseif ($key == 'REVIEWS_REPEAT') {
                    $arOptions[$key] = OptionReviews::setSiteParam($key, $requestValues[$key] ?: 0, $site);
                }
            }
        }
    }

    if ($requestValues['ENABLE_REVIEWS'] == 'Y') {
        $arOptions['ENABLE_REVIEWS'] = OptionReviews::setSiteParam('ENABLE_REVIEWS', $requestValues['ENABLE_REVIEWS'], $site);
        $isActive = true;
    } else {
        $arOptions['ENABLE_REVIEWS'] = OptionReviews::setSiteParam('ENABLE_REVIEWS', 'N', $site);
        $isActive = false;
    }
}

// Tabs list
$arTabs = [
    ['DIV' => 'edit1', 'TAB' => Loc::getMessage('edit1'), 'ICON' => '', 'TITLE' => Loc::getMessage('edit1'), 'SORT' => '10'],
    ['DIV' => 'edit2', 'TAB' => Loc::getMessage('edit2'), 'ICON' => '', 'TITLE' => Loc::getMessage('edit2'), 'SORT' => '20'],
    ['DIV' => 'edit3', 'TAB' => Loc::getMessage('edit3'), 'ICON' => '', 'TITLE' => Loc::getMessage('edit3'), 'SORT' => '30']
];

$formName = 'sotbit_reviews';
$tabControl = new CAdminForm($formName, $arTabs);
$tabControl->Begin();

// ------------------------------- Tab basic settings ------------------------

$tabControl->BeginNextFormTab();

// ------------------------------- Section basic settings --------------------

$tabControl->AddSection('GROUP_SETTINGS', Loc::getMessage('GROUP_SETTINGS'));

$tabControl->AddCheckBoxField("ENABLE_REVIEWS", Loc::getMessage('ENABLE'), false, 'Y',
    $arOptions["ENABLE_REVIEWS"] == 'N' ? '' : $arOptions["ENABLE_REVIEWS"], []);

$tabControl->AddCheckBoxField('REVIEWS_MODERATION', Loc::getMessage('MODERATION_REVIEWS'), false, 'Y',
    $arOptions["REVIEWS_MODERATION"] == 'N' ? '' : $arOptions["REVIEWS_MODERATION"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->AddCheckBoxField('REVIEWS_QUOTS', Loc::getMessage('REVIEWS_QUOTS'), false, 'Y',
    $arOptions["REVIEWS_QUOTS"] == 'N' ? '' : $arOptions["REVIEWS_QUOTS"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->AddCheckBoxField('REVIEWS_COPY', Loc::getMessage('REVIEWS_COPY'), false, 'Y',
    $arOptions["REVIEWS_COPY"] == 'N' ? '' : $arOptions["REVIEWS_COPY"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->AddCheckBoxField('REVIEWS_COMPLAINT', Loc::getMessage('REVIEWS_COMPLAINT'), false, 'Y',
    $arOptions["REVIEWS_COMPLAINT"] == 'N' ? '' : $arOptions["REVIEWS_COMPLAINT"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->AddDropDownField('REVIEWS_ID_ELEMENT', Loc::getMessage('REVIEWS_ID_ELEMENT'), false,
    $arIds, $arOptions['REVIEWS_ID_ELEMENT'], ($isActive ? [''] : [' disabled']));

$tabControl->AddCheckBoxField('REVIEWS_DELETE', Loc::getMessage('REVIEWS_DELETE'), false, 'Y',
    $arOptions["REVIEWS_DELETE"] == 'N' ? '' : $arOptions["REVIEWS_DELETE"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->AddCheckBoxField('REVIEWS_SCHEMA_ORG', Loc::getMessage('REVIEWS_SCHEMA_ORG'), false, 'Y',
    $arOptions["REVIEWS_SCHEMA_ORG"] == 'N' ? '' : $arOptions["REVIEWS_SCHEMA_ORG"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->BeginCustomField("REVIEWS_NOTICE_MAIL", '', false);
?>
<tr id="tr_REVIEWS_NOTICE_MAIL">
    <td width="40%" class="adm-detail-content-cell-l">
        <?= Loc::getMessage('REVIEWS_MAIL_LINK') ?>
    </td>
    <td class="adm-detail-content-cell-r">
        <?= $mailNotice ?>
    </td>
</tr>
<?php
$tabControl->EndCustomField("REVIEWS_NOTICE_MAIL");

// ------------------------------- Section user interaction --------------------

$tabControl->AddSection('GROUP_USER', Loc::getMessage('GROUP_USER'));

$tabControl->BeginCustomField("REVIEWS_NO_USER_IMAGE", '', false);
echo Admin::getFileField('REVIEWS_NO_USER_IMAGE', Loc::getMessage('NO_USER_IMAGE'), $arOptions['REVIEWS_NO_USER_IMAGE'], $isActive ? '' : 'N');
$tabControl->EndCustomField("REVIEWS_NO_USER_IMAGE");

$tabControl->AddCheckBoxField('REVIEWS_ANONYMOUS', Loc::getMessage('REVIEWS_ANONYMOUS'), false, 'Y',
    $arOptions["REVIEWS_ANONYMOUS"] == 'N' ? '' : $arOptions["REVIEWS_ANONYMOUS"], [0 => ($isActive ? '' : 'disabled')]);

if (Loader::includeModule('catalog') && Loader::includeModule('sale')) {
    $tabControl->AddCheckBoxField('REVIEWS_IF_BUY', Loc::getMessage('REVIEWS_IF_BUY'), false, 'Y',
        $arOptions["REVIEWS_IF_BUY"] == 'N' ? '' : $arOptions["REVIEWS_IF_BUY"], [0 => ($isActive ? '' : 'disabled')]);

    $tabControl->BeginCustomField("REVIEWS_ORDER_STATUS", '', false);
    echo Admin::getMultiSelectField("REVIEWS_ORDER_STATUS", Loc::getMessage('ORDER_STATUS_REVIEWS'), $orderStatus, $arOptions['REVIEWS_ORDER_STATUS'], ($isActive ? '' : 'disabled'), Loc::getMessage('ORDER_STATUS_NOTES_REVIEWS'));
    $tabControl->EndCustomField("REVIEWS_ORDER_STATUS");

    $tabControl->AddCheckBoxField('REVIEWS_PAYED', Loc::getMessage('PAYED_REVIEWS'), false, 'Y',
        $arOptions["REVIEWS_PAYED"] == 'N' ? '' : $arOptions["REVIEWS_PAYED"], [0 => ($isActive ? '' : 'disabled')]);
}

$tabControl->AddCheckBoxField('REVIEWS_EDIT', Loc::getMessage('REVIEWS_EDIT'), false, 'Y',
    $arOptions["REVIEWS_EDIT"] == 'N' ? '' : $arOptions["REVIEWS_EDIT"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->BeginCustomField("REVIEWS_ANONYMOUS_USER", '', false);
?>
<tr id="tr_REVIEWS_ANONYMOUS_USER">
    <td>
        <?= Loc::getMessage('REVIEWS_ANONYMOUS_USER') ?>
    </td>
    <td >
        <input type="checkbox" name="REVIEWS_ANONYMOUS_USER" value="Y" <?=$arOptions["REVIEWS_ANONYMOUS_USER"] == 'Y' ?'checked':''?>   <?= $isActive ? '' : 'disabled'?>>
        <span class="ui-hint" data-hint="<?= Loc::getMessage('REVIEWS_ANONYMOUS_USER_HINT') ?>" data-hint-html></span>
    </td>

</tr>
<?php
$tabControl->EndCustomField("REVIEWS_ANONYMOUS_USER");

$tabControl->BeginCustomField("REVIEWS_REPEAT", '', false);
echo Admin::getNumberField("REVIEWS_REPEAT", Loc::getMessage('REVIEWS_REPEAT'), $arOptions["REVIEWS_REPEAT"], '-1', Loc::getMessage('REVIEWS_REPEAT_NOTE'), ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_REPEAT");

// ------------------------------- Section Media Settings --------------------

$tabControl->AddSection('GROUP_MULTIMEDIA', Loc::getMessage('GROUP_MULTIMEDIA'));

$tabControl->AddDropDownField('REVIEWS_UPLOAD_FILE', Loc::getMessage('UPLOAD_FILE'), false,
    $arVariantFile, $arOptions['REVIEWS_UPLOAD_FILE'], ($isActive ? [''] : [' disabled']));


$tabControl->BeginCustomField("REVIEWS_MAX_IMAGE_SIZE", '', false);
echo Admin::getNumberField("REVIEWS_MAX_IMAGE_SIZE", Loc::getMessage('MAX_IMAGE_SIZE'), $arOptions["REVIEWS_MAX_IMAGE_SIZE"], 2, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_MAX_IMAGE_SIZE");

$tabControl->BeginCustomField("REVIEWS_MAX_COUNT_IMAGES", '', false);
echo Admin::getNumberField("REVIEWS_MAX_COUNT_IMAGES", Loc::getMessage('MAX_COUNT_IMAGES'), $arOptions["REVIEWS_MAX_COUNT_IMAGES"], 5, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_MAX_COUNT_IMAGES");

//new
$tabControl->BeginCustomField("REVIEWS_MAX_VIDEO_SIZE", '', false);
echo Admin::getNumberField("REVIEWS_MAX_VIDEO_SIZE", Loc::getMessage('REVIEWS_MAX_VIDEO_SIZE'), $arOptions["REVIEWS_MAX_VIDEO_SIZE"], 150, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_MAX_VIDEO_SIZE");

//new
$tabControl->BeginCustomField("REVIEWS_MAX_COUNT_VIDEO", '', false);
echo Admin::getNumberField("REVIEWS_MAX_COUNT_VIDEO", Loc::getMessage('REVIEWS_MAX_COUNT_VIDEO'), $arOptions["REVIEWS_MAX_COUNT_VIDEO"], 2, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_MAX_COUNT_VIDEO");

// ------------------------------- Tab Discounts and invoices --------------------

$tabControl->BeginNextFormTab();

// ------------------------------- Section discounts  ----------------------------

$tabControl->AddSection('GROUP_COUPONS', Loc::getMessage('GROUP_COUPONS'));

$tabControl->BeginCustomField("REVIEWS_ADD_COUPON", '', false);
?>
<tr id="tr_REVIEWS_ADD_COUPON">
    <td width="40%" class="adm-detail-content-cell-l">
        <?=Loc::getMessage('ADD_COUPON')?>
    </td>
    <td class="adm-detail-content-cell-r">
        <input type="checkbox"
               name="REVIEWS_ADD_COUPON"
               value="Y"
        <?= $arOptions["REVIEWS_ADD_COUPON"] == 'N' ? '' : 'checked'?>
        <?= $isActive ? '' : 'disabled'?>
        </label>
        <span class="ui-hint" data-hint="<?=Loc::getMessage('ADD_COUPON_HINT')?>" data-hint-html></span>
    </td>
</tr>
<?php
$tabControl->EndCustomField("REVIEWS_ADD_COUPON");

$tabControl->AddCheckBoxField('REVIEWS_SEND_MAIL', Loc::getMessage('SEND_MAIL'), false, 'Y',
    $arOptions["REVIEWS_SEND_MAIL"] == 'N' ? '' : $arOptions["REVIEWS_SEND_MAIL"], [0 => ($isActive ? '' : 'disabled')]);

$tabControl->BeginCustomField("REVIEWS_MAIL_BILL_LINK", '', false);
?>
<tr id="tr_REVIEWS_NOTICE_MAIL">
    <td width="40%" class="adm-detail-content-cell-l">
        <?= Loc::getMessage('REVIEWS_MAIL_BILL_LINK') ?>
    </td>
    <td class="adm-detail-content-cell-r">
        <?= $mailNoticeCoupon ?>
    </td>
</tr>
<?php
$tabControl->EndCustomField("REVIEWS_MAIL_BILL_LINK");

$tabControl->AddDropDownField('REVIEWS_ID_DISCOUNT', Loc::getMessage('ID_DISCOUNT'), false,
    $arDisc, $arOptions['REVIEWS_ID_DISCOUNT'], ($isActive ? [''] : [' disabled']));

$tabControl->AddDropDownField('REVIEWS_COUPON_TYPE', Loc::getMessage('COUPON_TYPE'), false,
    $arCoupon['TYPE'], $arOptions['REVIEWS_COUPON_TYPE'], ($isActive ? [''] : [' disabled']));

// ------------------------------- Section bill  ----------------------------

$tabControl->AddSection('GROUP_BILLS', Loc::getMessage('GROUP_BILLS'));

$tabControl->BeginCustomField("REVIEWS_BILL_ADD_REVIEW", '', false);
echo Admin::getNumberField("REVIEWS_BILL_ADD_REVIEW", Loc::getMessage('REVIEWS_BILL_ADD_REVIEW'), $arOptions["REVIEWS_BILL_ADD_REVIEW"], 0, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_BILL_ADD_REVIEW");

$tabControl->BeginCustomField("REVIEWS_BILL_LIKE_REVIEW", '', false);
echo Admin::getNumberField("REVIEWS_BILL_LIKE_REVIEW", Loc::getMessage('REVIEWS_BILL_LIKE_REVIEW'), $arOptions["REVIEWS_BILL_LIKE_REVIEW"], 0, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_BILL_LIKE_REVIEW");

$tabControl->BeginCustomField("REVIEWS_BILL_DISLIKE_REVIEW", '', false);
echo Admin::getNumberField("REVIEWS_BILL_DISLIKE_REVIEW", Loc::getMessage('REVIEWS_BILL_DISLIKE_REVIEW'), $arOptions["REVIEWS_BILL_DISLIKE_REVIEW"], 0, null, ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_BILL_DISLIKE_REVIEW");

$tabControl->AddDropDownField('REVIEWS_BILL_CURRENCY_REVIEW', Loc::getMessage('REVIEWS_BILL_CURRENCY_REVIEW'), false,
    $currencies, $arOptions['REVIEWS_BILL_CURRENCY_REVIEW'], ($isActive ? [''] : [' disabled']));

$tabControl->BeginCustomField("REVIEWS_BILL_GROUP", '', false);
echo Admin::getMultiSelectField("REVIEWS_BILL_GROUP", Loc::getMessage('REVIEWS_BILL_GROUP'), $groups, $arOptions['REVIEWS_BILL_GROUP'], ($isActive ? '' : 'disabled'));
$tabControl->EndCustomField("REVIEWS_BILL_GROUP");

// ------------------------------- Tab Protection against bots and spam --------------------

//$tabControl->BeginNextFormTab();
//
//// ------------------------------- Section Recaptha2 settings ------------------------------
//
//$tabControl->AddSection('GROUP_RECAPTCHA2', Loc::getMessage('GROUP_RECAPTCHA2'));
//
//$tabControl->AddEditField('REVIEWS_RECAPTCHA2_SITE_KEY', Loc::getMessage('REVIEWS_RECAPTCHA2_SITE_KEY'), false, [0 => ($isActive ? '' : 'disabled')], '');
//$tabControl->AddEditField('REVIEWS_RECAPTCHA2_SECRET_KEY', Loc::getMessage('REVIEWS_RECAPTCHA2_SECRET_KEY'), false, [0 => ($isActive ? '' : 'disabled')], '');
//
//// ------------------------------- Section Akismet Settings --------------------------------
//
//$tabControl->AddSection('GROUP_AKISMET', Loc::getMessage('GROUP_AKISMET'));
//
//$tabControl->AddEditField('REVIEWS_AKISMET_API_KEY', Loc::getMessage('REVIEWS_AKISMET_API_KEY'), false, [0 => ($isActive ? '' : 'disabled')], '');
//$tabControl->AddEditField('REVIEWS_AKISMET_API_LOGIN', Loc::getMessage('REVIEWS_AKISMET_API_LOGIN'), false, [0 => ($isActive ? '' : 'disabled')], '');

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
