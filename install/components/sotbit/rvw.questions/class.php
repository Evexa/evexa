<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;
use Sotbit\Reviews\Helper\HelperComponent;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('sotbit.reviews');

class Questions extends CBitrixComponent implements Controllerable, \Bitrix\Main\Errorable
{
    public int $userId;
    protected ErrorCollection $errorCollection;

    public function __construct($component = null)
    {
        parent::__construct($component);
    }

    public function configureActions()
    {
        return [
            'contentLoader' => [
                'prefilters' => [],
                'postfilters' => []
            ],
        ];
    }

    public function listKeysSignedParameters()
    {
        return [
            "ID_ELEMENT",
            "TEXTBOX_MAXLENGTH",
            "DATE_FORMAT",
            "NOTICE_EMAIL",
            "COUNT_PAGE",
            "USER_AGREEMENT_ID",
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams["TEXTBOX_MAXLENGTH"] = $arParams["TEXTBOX_MAXLENGTH"] ?: 100;
        $arParams["DATE_FORMAT"] = $arParams["DATE_FORMAT"] ?: "d F Y";
        $arParams["ID_ELEMENT"] = $arParams["ID_ELEMENT"] ?: "0";
        $arParams["NOTICE_EMAIL"] = $arParams["NOTICE_EMAIL"] ?: "";
        $arParams["COUNT_PAGE"] = $arParams["COUNT_PAGE"] ?: "10";

        $this->userId = (int)\Bitrix\Main\Engine\CurrentUser::get()->getId();
        $this->errorCollection = new ErrorCollection();

        return $arParams;
    }

    public function executeComponent()
    {
        if (!HelperComponent::checkModule()) {
            ShowError(Loc::getMessage("SOTBIT_REVIEWS_ERROR_MODULE_LOADER"));
            return 0;
        }

        if (HelperComponent::checkActive('ENABLE_QUESTIONS')) {
            ShowError(Loc::getMessage("SOTBIT_REVIEWS_ERROR_MODULE_ACTIVE"));
            return 0;
        }

        $componentVariables = [];
        $variables = [];

        $variableAliases = CComponentEngine::makeComponentVariableAliases([],
            $this->arParams["VARIABLE_ALIASES"]);
        CComponentEngine::initComponentVariables(false, $componentVariables, $variableAliases, $variables);

        $componentPage = "list";

        $this->arResult = [
            "VARIABLES" => $variables,
            "ALIASES" => $variableAliases
        ];

        global $APPLICATION;
        $this->arResult = [
            "FOLDER" => "",
            "URL_TEMPLATES" => ["list" => htmlspecialcharsbx($APPLICATION->GetCurPage())],
            "VARIABLES" => $variables,
            "ALIASES" => $variableAliases
        ];

        if ($componentPage == "index" && $this->getTemplateName() !== "") {
            $componentPage = "template";
        }

        $this->includeComponentTemplate($componentPage);
    }

    public function contentLoaderAction()
    {
        global $APPLICATION;

        if (!HelperComponent::checkModule()) {
            $this->errorCollection[] = new Error(Loc::getMessage("SOTBIT_REVIEWS_ERROR_MODULE_LOADER"));
            return null;
        }

        $replaceUrl = str_replace($_SERVER['HTTP_ORIGIN'], "",$_SERVER['HTTP_REFERER']);
        $server = $this->request->getServer()->getValues();
        $server['REQUEST_URI'] = $replaceUrl;
        $this->request->getServer()->set($server);
        $APPLICATION->SetCurPage($replaceUrl);

        $obComponentResult = new \Bitrix\Main\Engine\Response\Component('sotbit:rvw.questions', $this->request->get('templateName') ?? '', $this->arParams);
        return $obComponentResult ?: null;
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }
}
