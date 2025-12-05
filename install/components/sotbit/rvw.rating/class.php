<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\{Loader, Localization\Loc};
use Sotbit\Reviews\{Helper\HelperComponent, Internals\ReviewsTable};

class Rating extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['ELEMENT_ID'] = (int)$arParams['ELEMENT_ID'];
        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME'])
            ? (int)$arParams['CACHE_TIME']
            : 3600;

        return $arParams;
    }

    public function executeComponent(): void
    {
        if (!Loader::includeModule('sotbit.reviews')) {
            ShowError(Loc::getMessage('SOTBIT_REVIEWS_ERROR_MODULE_LOADER'));
            return;
        }

        if (HelperComponent::checkActive('ENABLE_REVIEWS')) {
            ShowError(Loc::getMessage("SOTBIT_REVIEWS_ERROR_MODULE_ACTIVE"));
            return;
        }

        if ($this->arParams['ELEMENT_ID'] < 1) {
            ShowError(Loc::getMessage('SR_COMPONENT_INVALID_ELEMENT_ID'));
            return;
        }

        if ($this->startResultCache()) {
            try {
                $this->arResult['REVIEWS_COUNT'] = ReviewsTable::getCount(
                    [
                        'ID_ELEMENT' => $this->arParams['ELEMENT_ID'],
                        'MODERATED' => 'Y',
                        'ACTIVE' => 'Y'
                    ]
                );

                $this->arResult['RATING'] = number_format(
                    HelperComponent::getArrayRaiting(
                        'ID_ELEMENT',
                        $this->arParams['ELEMENT_ID'],
                        $this->arResult['REVIEWS_COUNT']
                    ),
                    1
                );
            } catch (Exception) {
                $this->abortResultCache();
                ShowError(Loc::getMessage('SR_COMPONENT_RATING_ERROR'));
                return;
            }

            $this->setResultCacheKeys([]);
            $this->includeComponentTemplate();
        }
    }
}
