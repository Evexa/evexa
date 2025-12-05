<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

$arComponentDescription = array(
	"NAME" => Loc::getMessage('SR_COMPONENT_REVIEWS_NAME'),
	"DESCRIPTION" => Loc::getMessage('SR_COMPONENT_REVIEWS_DESCRIPTION'),
	"SORT" => 2,
	"COMPLEX" => "Y",
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
