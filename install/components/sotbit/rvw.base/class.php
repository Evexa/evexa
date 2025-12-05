<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter;
use Sotbit\Reviews\Helper\HelperComponent;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('sotbit.reviews');

class ReviewsComponent extends \CBitrixComponent implements Controllerable
{
    protected $moduleName = 'sotbit.reviews';

    public function configureActions()
    {
        return [
            'contentLoader' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams["SHOW_REVIEWS"] = $arParams["SHOW_REVIEWS"] ?: 'Y';
        $arParams["SHOW_QUESTIONS"] = $arParams["SHOW_QUESTIONS"] ?: 'Y';
        $arParams["FIRST_ACTIVE"] = $arParams["FIRST_ACTIVE"] ?: 1;
        $arParams["MAX_RATING"] = $arParams["MAX_RATING"] ?: 5;
        $arParams["DEFAULT_RATING_ACTIVE"] = $arParams["DEFAULT_RATING_ACTIVE"] ?: 3;

        $arParams["REVIEWS_TEXTBOX_MAXLENGTH"] = $arParams["REVIEWS_TEXTBOX_MAXLENGTH"] ?: 100;
        $arParams["QUESTIONS_TEXTBOX_MAXLENGTH"] = $arParams["QUESTIONS_TEXTBOX_MAXLENGTH"] ?: 100;

        $arParams["CACHE_TIME"] = $arParams["CACHE_TIME"] ?: 36000000;
        $arParams["CACHE_TYPE"] = $arParams["CACHE_TYPE"] ?: "A";

        $arParams["DATE_FORMAT"] = $arParams["DATE_FORMAT"] ?: "d F Y";

        return $arParams;
    }

    public function executeComponent()
    {
        if (!HelperComponent::checkModule()) {
            ShowError(Loc::getMessage("SOTBIT_REVIEWS_ERROR_MODULE_LOADER"));
            return 0;
        }

        $this->includeComponentTemplate();
    }

    public function listKeysSignedParameters()
    {
        return [
            "DEFAULT_RATING_ACTIVE",
            "MAX_RATING",
            "ID_ELEMENT",
            "DATE_FORMAT",
            "CACHE_TIME",
            "CACHE_GROUPS",
            "NOTICE_EMAIL",
            "AJAX",
            "USER_AGREEMENT_ID",
        ];
    }

    public function contentLoaderAction($active)
    {
        global $APPLICATION;
        $APPLICATION->SetCurPage(str_replace( $_SERVER['HTTP_ORIGIN'], "",$_SERVER['HTTP_REFERER']));

        $arParams = [
            'DEFAULT_RATING_ACTIVE' => $this->arParams['DEFAULT_RATING_ACTIVE'],
            'MAX_RATING' => $this->arParams['MAX_RATING'],
            'ID_ELEMENT' => $this->arParams['ID_ELEMENT'],
            "DATE_FORMAT" => $this->arParams['DATE_FORMAT'],
            'CACHE_TIME' => $this->arParams["CACHE_TIME"],
            'CACHE_GROUPS' => $this->arParams["CACHE_GROUPS"],
            "NOTICE_EMAIL" => $this->arParams['NOTICE_EMAIL'],
            "USER_AGREEMENT_ID" => $this->arParams["USER_AGREEMENT_ID"],
        ];

        if (!HelperComponent::checkModule()) {
            $this->errorCollection[] = new Error(Loc::getMessage("SOTBIT_REVIEWS_ERROR_MODULE_LOADER"));
            return null;
        }

        $obComponentResult = new \Bitrix\Main\Engine\Response\Component('sotbit:rvw.' . $active, '', $arParams);

        return $obComponentResult ?: null;
    }
}
?>
