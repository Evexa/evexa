<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\{
    Localization\Loc,
    Security\Random
};

/** @var array $arParams */
/** @var array $arResult */

if (!$arParams['ID']) {
    return;
}

$componentId = Random::getString(6);
?>

<button id="user-consent-view-button-<?= $componentId ?>"
        class="user-consent-view-button"
        type="button"
><?= $arParams['~LABEL'] ?></button>

<script>
    BX.ready(function () {
        BX.message(<?= CUtil::PhpToJSObject(Loc::loadLanguageFile(__FILE__)) ?>);
    });
    new UserConsentView(<?= CUtil::PhpToJSObject([
        'text' => nl2br(htmlspecialcharsbx($arResult['TEXT'])),
        'buttonId' => "user-consent-view-button-$componentId",
        'title' => $arResult['TITLE'],
    ]) ?>);

</script>
