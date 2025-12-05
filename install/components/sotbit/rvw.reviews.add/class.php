<?php

use Bitrix\Main\Loader,
    Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Application,
    Bitrix\Main\Localization\Loc;
use Bitrix\Fileman\Block\Sanitizer;

use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Helper\HelperComponent;
use Sotbit\Reviews\Internals\ReviewsTable;
use Sotbit\Reviews\Security\Security;
use Sotbit\Reviews\Model\Reviews as ModelReviews;
use Sotbit\Reviews\Logic\Addition;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('sotbit.reviews');

CBitrixComponent::includeComponentClass("sotbit:rvw.reviews");

class ReviewsAdd extends Reviews implements Controllerable
{
    protected bool $canAddReview = true;
    protected string $errorMessage = '';

    public function configureActions()
    {
        return [
            'addReviews' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ]
        ];
    }

    public function listKeysSignedParameters()
    {
        return [
            "MAX_RATING",
            "DEFAULT_RATING_ACTIVE",
            "TEXTBOX_MAXLENGTH",
            "ID_ELEMENT",
            "AJAX",
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        if (Loader::includeModule('sotbit.reviews') || !\CSotbitReviews::getDemo()) {
            parent::onPrepareComponentParams($arParams);
            $arParams['FIELD_ELEMENT'] = OptionReviews::getConfig('REVIEWS_ID_ELEMENT', SITE_ID);
        }
        $this->arParams = $arParams;

        return $arParams;
    }

    public function executeComponent()
    {
        if (!HelperComponent::checkModule()) {
            return;
        }

        $isAdding = new Addition('REVIEWS', $this->userId, $this->arParams['ID_ELEMENT']);

        $this->arResult['CAN_ADD_REVIEW'] = $isAdding->result['CAN_ADD'] ?: false;
        $this->arResult['CAN_ADD_REVIEW_ERROR'] = $isAdding->result['CAN_ADD_ERROR'];

        $this->getResult();
        $this->includeComponentTemplate();
    }

    private function getResult()
    {
        Loader::includeModule('iblock');

        $res = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['ID' => $this->arParams['ID_ELEMENT']],
            'select' => ['ID', 'IBLOCK_ID', 'NAME', "PREVIEW_PICTURE", "DETAIL_PICTURE"],
        ])->fetch();

        if(
            Loader::includeModule('catalog')
            && empty($res['PREVIEW_PICTURE'])
            && empty($res['DETAIL_PICTURE'])
            && CCatalogSKU::getExistOffers([$this->arParams['ID_ELEMENT']])
        ) {
            $resOffers = CCatalogSKU::getOffersList(
                $this->arParams['ID_ELEMENT'], 0,
                [],
                ['PREVIEW_PICTURE', 'DETAIL_PICTURE'],
            );
            if (is_array($resOffers)) {
                foreach ($resOffers[$this->arParams['ID_ELEMENT']] as $offer) {
                    if ($offer['PREVIEW_PICTURE'] || $offer['DETAIL_PICTURE']) {
                        $res['PREVIEW_PICTURE'] = $offer['PREVIEW_PICTURE'];
                        $res['DETAIL_PICTURE'] = $offer['DETAIL_PICTURE'];
                        break;
                    }
                }
            }
        }

        if (is_array($res)) {
            foreach (['PREVIEW_PICTURE', 'DETAIL_PICTURE'] as $pictureFieldName) {
                if ($res[$pictureFieldName]) {
                    $resFileItem = CFile::GetByID($res[$pictureFieldName])->Fetch();
                    $res[$pictureFieldName] = $resFileItem;
                }
            }
        }

        $this->arResult['ELEMENT'] = $res;
    }

    public function addReviewsAction()
    {
        $isAdded = new Addition('REVIEWS', $this->userId, $this->arParams['ID_ELEMENT']);

        if ($isAdded->result['CAN_ADD_ERROR']) {
            return \Sotbit\Reviews\Helper\Error::getAjaxError($isAdded->result['CAN_ADD_ERROR']);
        }

        $request = $this->request->getValues();

        $request['COMMENT'] = Loader::includeModule('fileman')
            ? Sanitizer::clean($request['COMMENT'])
            : strip_tags($request['COMMENT']);

        return ModelReviews::add($request, $this->arParams['ID_ELEMENT']);
    }
}
?>
