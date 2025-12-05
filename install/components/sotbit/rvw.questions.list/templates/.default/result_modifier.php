<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Sotbit\Reviews\Helper\OptionReviews;

$config = OptionReviews::getConfigsQuestions(SITE_ID);

$arResult['USER_ID'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
$arResult['IS_AUTHORIZE_USER'] = \Bitrix\Main\Engine\CurrentUser::get()->getId() ? true : false;
$arResult['QUESTIONS_SCHEMA_ORG'] = $config['QUESTIONS_SCHEMA_ORG'];
$arResult['QUESTIONS_NO_USER_IMAGE'] = $config['QUESTIONS_NO_USER_IMAGE'];
$arResult['QUESTIONS_EDIT'] = $config['QUESTIONS_EDIT'];

