<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
}

use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;

if (Loader::includeModule('sotbit.reviews')) {
    $config = OptionReviews::getConfigs(SITE_ID);
    $arResult['QUESTIONS_ANONYMOUS'] = $config['QUESTIONS_ANONYMOUS'];
    $arResult['QUESTIONS_ANONYMOUS_USER'] = $config['QUESTIONS_ANONYMOUS_USER'];
    $arResult['QUESTIONS_MODERATION'] = $config['QUESTIONS_MODERATION'];
    $arResult['QUESTIONS_REPEAT'] = $config['QUESTIONS_REPEAT'];
}

