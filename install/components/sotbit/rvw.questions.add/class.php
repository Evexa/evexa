<?php

use Bitrix\Main\Loader,
    Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Application,
    Bitrix\Main\Localization\Loc;
use Bitrix\Fileman\Block\Sanitizer;

use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Helper\HelperComponent;
use Sotbit\Reviews\Internals\QuestionsTable;
use Sotbit\Reviews\Model\Questions as ModelQuestions;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('sotbit.reviews');

CBitrixComponent::includeComponentClass("sotbit:rvw.questions");

class QuestionsAdd extends Questions
{
    protected bool $canAddReview = true;
    protected string $errorMessage = '';

    public function configureActions()
    {
        return [
            'addQuestions' => [
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
            "ID_ELEMENT",
            "TEXTBOX_MAXLENGTH",
            "AJAX",
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        if (Loader::includeModule('sotbit.reviews') || !\CSotbitReviews::getDemo()) {
            parent::onPrepareComponentParams($arParams);
            $arParams['FIELD_ELEMENT'] = OptionReviews::getConfig('QUESTIONS_ID_ELEMENT', SITE_ID);
        }
        $this->arParams = $arParams;
        return $arParams;
    }

    public function executeComponent()
    {
        if (!HelperComponent::checkModule()) {
            return;
        }

        $result = new Sotbit\Reviews\Logic\Addition('QUESTIONS', $this->userId, $this->arParams['ID_ELEMENT']);
        $this->arResult['CAN_ADD_QUESTIONS'] = $result->result['CAN_ADD'];
        $this->arResult['CAN_ADD_QUESTIONS_ERROR'] = $result->result['CAN_ADD_ERROR'];

        if (Loader::includeModule('sotbit.reviews')) {
            $config = OptionReviews::getConfigs(SITE_ID);
            $this->arResult['QUESTIONS_ANONYMOUS'] = $config['QUESTIONS_ANONYMOUS'];
            $this->arResult['QUESTIONS_ANONYMOUS_USER'] = $config['QUESTIONS_ANONYMOUS_USER'];
            $this->arResult['QUESTIONS_MODERATION'] = $config['QUESTIONS_MODERATION'];
            $this->arResult['QUESTIONS_REPEAT'] = $config['QUESTIONS_REPEAT'];
        }

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

    public function addQuestionsAction()
    {
        $isAdded = new Sotbit\Reviews\Logic\Addition('QUESTIONS', $this->userId, $this->arParams['ID_ELEMENT']);

        if ($isAdded->result['CAN_ADD_ERROR']) {
            return \Sotbit\Reviews\Helper\Error::getAjaxError($isAdded->result['CAN_ADD_ERROR']);
        }

        $request = $this->request->getValues();

        $request['QUESTION'] = Loader::includeModule('fileman')
            ? Sanitizer::clean($request['QUESTION'])
            : strip_tags($request['QUESTION']);

        return ModelQuestions::add($request, $this->arParams['ID_ELEMENT']);
    }
}

?>
