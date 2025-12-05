<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Helper\HelperComponent;
use Sotbit\Reviews\Logic\Component;

Loader::includeModule('sotbit.reviews');

CBitrixComponent::includeComponentClass("sotbit:rvw.reviews");

class ReviewsFilter extends Reviews
{
    public $productField = '';

    public function onPrepareComponentParams($arParams)
    {
        parent::onPrepareComponentParams($arParams);
        return $arParams;
    }

    public function executeComponent()
    {
        if (!HelperComponent::checkModule()) {
            return;
        }
        $this->productField = OptionReviews::getConfig('REVIEWS_ID_ELEMENT', SITE_ID);
        $this->requestValue = $this->request->getValues();

        $this->arResult['COUNT_ITEM_FILE'] = Component::getCountItemFiles($this->arParams['ID_ELEMENT'],$this->productField, $this->requestValue['RATING']);
        $this->arResult['RATING'] = Component::getArrayRating($this->arParams['ID_ELEMENT'],$this->productField, $this->requestValue);

        $this->includeComponentTemplate();
    }
}
?>