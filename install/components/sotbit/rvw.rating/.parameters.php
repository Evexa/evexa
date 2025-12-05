<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    "GROUPS" => [],
    "PARAMETERS" => [
        "ELEMENT_ID" => [
            "PARENT" => "BASE",
            "NAME" => Loc::getMessage("SR_COMPONENT_PARAMETER_RATING_ELEMENT_ID"),
            "TYPE" => "STRING",
        ],
        "CACHE_TIME" => [
            "DEFAULT" => 3600
        ],
    ],
];
