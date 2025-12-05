<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Sotbit\Reviews\Helper\OptionReviews;

$config = OptionReviews::getConfigsReviews(SITE_ID);

$arResult['USER_ID'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
$arResult['IS_AUTHORIZE_USER'] = \Bitrix\Main\Engine\CurrentUser::get()->getId() ? true : false;
$arResult['REVIEWS_SCHEMA_ORG'] = $config['REVIEWS_SCHEMA_ORG'];
$arResult['REVIEWS_NO_USER_IMAGE'] = $config['REVIEWS_NO_USER_IMAGE'];
$arResult['REVIEWS_COMPLAINT'] = $config['REVIEWS_COMPLAINT'];
$arResult['REVIEWS_EDIT'] = $config['REVIEWS_EDIT'];
$arResult['REVIEWS_QUOTS'] = $config['REVIEWS_QUOTS'];
$arResult['REVIEWS_COPY'] = $config['REVIEWS_COPY'];

