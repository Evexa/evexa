<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserConsent\Internals\AgreementTable;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Helper\HelperComponent;
use Sotbit\Reviews\Internals;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('sotbit.reviews');

CBitrixComponent::includeComponentClass("sotbit:rvw.reviews");

class ReviewsStatistics extends Reviews
{

    public function onPrepareComponentParams($arParams)
    {
        parent::onPrepareComponentParams($arParams);
        $arParams["MAX_RATING"] = $arParams["MAX_RATING"] ?: 5;
        return $arParams;
    }

    public function executeComponent()
    {
        if (!HelperComponent::checkModule()) {
            return;
        }

        $this->getResult();
        $this->includeComponentTemplate();
    }

    public function getCount($IDx)
    {
        $res = Internals\ReviewsTable::GetCount([
            'MODERATED' => 'Y',
            'ACTIVE' => 'Y',
            $IDx => $this->arParams['ID_ELEMENT']
        ]);
        return $res;
    }

    public function getArrayRaiting($IDx)
    {
        $result = [];
        $rating = [];

        $res = Internals\ReviewsTable::getList([
            'filter' => [
                'MODERATED' => 'Y',
                'ACTIVE' => 'Y',
                $IDx => $this->arParams['ID_ELEMENT']
            ],
            'select' => ['RATING', 'ID_ELEMENT'],
        ])->fetchAll();

        if (is_array($res)) {
            foreach ($res as $item) {
                $result[$item['RATING']] += 1;
                $rating[] += $item['RATING'];
            }
            if(is_array($result)){
                krsort($result);
            }
        } else {
            $result = [];
        }
        foreach ($result as $star => $value){
            $this->arResult['AVERAGE_RATING_ITEM'][$star] = round($value / $this->arResult['COUNT'] * 100) ?: 0;
        }

        if($this->arResult['COUNT'] > 0 && is_array($rating)){
            $this->arResult['AVERAGE_RATING'] = round(array_sum($rating) / $this->arResult['COUNT'], 1);
        }else{
            $this->arResult['AVERAGE_RATING'] = 0;
        }
        return $result;
    }

    public function getFiles($IDx)
    {
        $res = Internals\ReviewsTable::getList([
            'filter' => [$IDx => $this->arParams['ID_ELEMENT'], '!=FILES' => 'N;', 'ACTIVE' => 'Y', 'MODERATED' => 'Y'],
            'select' => ['FILES'],
            'group' => ['FILES'],
            'data_doubling' => false,
        ])->fetchAll();

        foreach ($res as $item) {
            $arFile = unserialize($item['FILES']);
            foreach ($arFile as $file) {
                $arIdFile[$file] = $file;
            }
        }

        if (!empty($arIdFile)) {
            $resFile = \Bitrix\Main\FileTable::getList([
                'order' => ['TIMESTAMP_X' => 'DESC'],
                'filter' => ['ID' => $arIdFile],
                'select' => ['*']
            ])->fetchAll();


            foreach ($resFile as $key => $itemFile) {
                $resFile[$key]['SRC'] = htmlspecialchars('/upload/' .$itemFile['SUBDIR'] .'/'. $itemFile['FILE_NAME'], ENT_QUOTES);
            }

            return $resFile;
        }else{
            return [];
        }
    }

    private function getResult()
    {
        $IDx = OptionReviews::getConfig('REVIEWS_ID_ELEMENT', SITE_ID) == 'ID_ELEMENT' ? 'ID_ELEMENT': 'XML_ID_ELEMENT';

        $this->arResult['COUNT'] = $this->getCount($IDx);
        if($this->arResult['COUNT'] > 0){
            $this->arResult['FILES'] = $this->getFiles($IDx);
            $this->arResult['RATING'] = $this->getArrayRaiting($IDx);
        }

        if ($this->arParams['USER_AGREEMENT_ID']) {
            $this->arResult['USER_AGREEMENT_SECURITY_CODE'] = AgreementTable::query()
                ->setSelect(['SECURITY_CODE'])
                ->where('ID', $this->arParams['USER_AGREEMENT_ID'])
                ->fetch()['SECURITY_CODE'];
        }
    }
}

?>
