<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

$arTemplateParameters['LABEL'] = array(
    'PARENT' => 'BASE',
    'NAME' => Loc::getMessage('USER_CONSENT_VIEW_LABEL_NAME'),
    'TYPE' => 'STRING',
    'DEFAULT' => Loc::getMessage('USER_CONSENT_VIEW_LABEL_DEFAULT'),
);