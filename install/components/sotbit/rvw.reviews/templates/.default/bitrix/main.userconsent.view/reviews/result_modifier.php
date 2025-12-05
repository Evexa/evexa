<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\UserConsent\Agreement;

/**
 * @var array $arParams
 * @var array $arResult
 */

$agreement = new Agreement($arParams['ID']);
$arResult['TITLE'] = $agreement->getData()['NAME'] ?? $arResult['TITLE'];
