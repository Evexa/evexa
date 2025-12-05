<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME" => Loc::getMessage('SR_COMPONENT_REVIEWS_ADD_NAME'),
	"DESCRIPTION" => Loc::getMessage('SR_COMPONENT_REVIEWS_ADD_DESCRIPTION'),
	"SORT" => 3,
	"PATH" => array(
		"ID" => "sotbit",
		"CHILD" => array(
			"ID" => "reviews",
			"NAME" => Loc::getMessage('SR_COMPONENT_MODULE_NAME'),
			"SORT" => 362,
		),
	),
);
?>
