<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\Loader,
    Bitrix\Main\Engine\Contract\Controllerable,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Error,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Application,
    Bitrix\Main\Localization\Loc;

use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Helper\HelperComponent;
use Sotbit\Reviews\Internals;
use Sotbit\Reviews\Model;

Loader::includeModule('sotbit.reviews');

CBitrixComponent::includeComponentClass("sotbit:rvw.questions");

class QuestionsList extends Questions
{
    public function configureActions()
    {
        return [
            'like' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                ],
                'postfilters' => []
            ],
            'edit' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        array(ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST)
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }

    public function listKeysSignedParameters()
    {
        return [
            "DATE_FORMAT",
            "ID_ELEMENT",
            "COUNT_PAGE",
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        parent::onPrepareComponentParams($arParams);
        $arParams["COUNT_PAGE"] = $arParams["COUNT_PAGE"] ?: 10;
        $arParams["DATE_FORMAT"] = $arParams["DATE_FORMAT"] ?: "d F Y";
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

    private function getResult()
    {
        Loader::includeModule('iblock');
        Loader::includeModule('main');

        $configID = OptionReviews::getConfig('QUESTIONS_ID_ELEMENT', SITE_ID);
        $IDx = ($configID == 'ID_ELEMENT' ? 'ID_ELEMENT' : 'XML_ID_ELEMENT');

        $filter = ['=' . $IDx => $this->arParams['ID_ELEMENT'], '=ACTIVE' => 'Y', '=MODERATED' => 'Y'];
        $limit = $this->arParams['COUNT_PAGE'];

        $this->getNav($filter, $limit);

        $rsData = Internals\QuestionsTable::getList(
            [
                'select' => ['*', 'DATE_CREATION_ORIG' => 'DATE_CREATION', 'DATE_CREATION',],
                'filter' => $filter,
                'order' => ['DATE_CREATION' => 'desc'],
                'limit' => $this->nav->getLimit(),
                'offset' => $this->nav->getOffset(),
                'cache' => ['ttl' => 3600, 'cache_joins' => true],
            ]);

        $arQuestions = $rsData->fetchAll();

        $arUserQuestion = [];
        $questionsIds = [];

        foreach ($arQuestions as $key => $questionsItem) {
            $questionsIds[] = $questionsItem['ID'];
            $arUserQuestion[$key] = $questionsItem['ID_USER'];

            $questionsItem['DATE_CREATION'] = \CIBlockFormatProperties::DateFormat(
                $this->arParams["DATE_FORMAT"], MakeTimeStamp($questionsItem['DATE_CREATION'], CSite::GetDateFormat())
            );

            $arResult['QUESTIONS'][$key] = $questionsItem;
        }

        if (is_array($arUserQuestion)) {
            $this->writeFieldUsers($arResult, $arUserQuestion);
        }

        $arResult["CNT_LEFT_PGN"] = 3;
        $arResult["CNT_RIGHT_PGN"] = 3;
        $arResult['QUESTIONS_IDS'] = serialize($arUserQuestion);

        $likeResult = $this->getLikesUser($questionsIds);
        $arResult['LIKES'] = $likeResult['LIKES'];
        $arResult['DISLIKES'] = $likeResult['DISLIKES'];

        $this->arResult = $arResult;
    }

    public function writeFieldUsers(&$arResult, $arUserQuestion)
    {
        $arUsers = [];

        $resUser = \Bitrix\Main\UserTable::getList([
            'order' => ['TIMESTAMP_X' => 'ASC'],
            'filter' => ['=ID' => $arUserQuestion],
            'select' => [
                'ID',
                'PERSONAL_PHOTO_SRC',
                'PERSONAL_PHOTO_ID' => 'PERSONAL_PHOTO',
                'FULL_NAME',
                'PERSONAL_BIRTHDAY' => 'PERSONAL_BIRTHDAY',
            ],
            'runtime' => [
                new \Bitrix\Main\Entity\ExpressionField(
                    'FULL_NAME',
                    'CONCAT(%s, " ", %s)',
                    ['NAME', 'LAST_NAME']
                ),
                new Reference(
                    'PHOTO_USER',
                    \Bitrix\Main\FileTable::class,
                    Join::on('this.PERSONAL_PHOTO', 'ref.ID')
                ),
                new \Bitrix\Main\Entity\ExpressionField(
                    'PERSONAL_PHOTO_SRC',
                    'CONCAT("/upload/" ,%s, "/", %s)',
                    ['PHOTO_USER.SUBDIR', 'PHOTO_USER.FILE_NAME']
                ),
            ],
            'cache' => ['ttl' => 3600, 'cache_joins' => true],
        ]);
        while ($arUser = $resUser->fetch()) {
            if ($arUser['PERSONAL_PHOTO_ID'] > 0) {
                $arFileResize = CFile::ResizeImageGet($arUser['PERSONAL_PHOTO_ID'],
                    array('width' => 100, 'height' => 100), BX_RESIZE_IMAGE_EXACT);
                $arUser['PERSONAL_PHOTO_SRC'] = $arFileResize['src'];
            } else {
                $arUser['PERSONAL_PHOTO_SRC'] = OptionReviews::getConfig('QUESTIONS_NO_USER_IMAGE');
            }
            $id = $arUser['ID'];
            unset($arUser['ID']);
            $arUsers[$id] = $arUser;
        }

        foreach ($arResult['QUESTIONS'] as &$itemReview) {
            if ($itemReview['ID_USER'] > 0 && is_array($arUsers[$itemReview['ID_USER']])) {
                $itemReview = array_merge($itemReview, $arUsers[$itemReview['ID_USER']]);
            }
        }
    }

    public function getNav($filter, $limit)
    {
        $cnt = Internals\QuestionsTable::getCount($filter);
        $nav = new \Bitrix\Main\UI\PageNavigation("queestion");
        $nav->allowAllRecords(true)
            ->setPageSize($limit)
            ->initFromUri();
        $nav->setRecordCount($cnt);
        if ($this->request->get('more')) {
            $nav->setCurrentPage($this->request->get('more'));
        }

        $this->nav = $nav;
    }

    public function getCountUserReviews($arUserID): array
    {
        $rsData = Internals\QuestionsTable::getList([
            'select' => ['ID_USER'],
            'filter' => ['=ID_USER' => $arUserID, '=ACTIVE' => 'Y', '=MODERATED' => 'Y'],
            'cache' => ['ttl' => 3600, 'cache_joins' => true],
        ]);

        $result = [];
        while ($review = $rsData->fetch()) {
            $result[$review['ID_USER']] = ($result[$review['ID_USER']] ?? 0) + 1;
        }
        return $result;
    }

    public function getLikesUser($questionsIds): array
    {
        $arLike = Internals\LikeTable::getList([
            'filter' => [
                "LOGIC" => "OR",
                ["=IP" => $_SERVER["REMOTE_ADDR"], ["QUESTION_ID" => $questionsIds]],
                ["=ID_USER" => $this->userId, ["QUESTION_ID" => $questionsIds]],
            ],
            'select' => ['QUESTION_ID', 'LIKE', 'DISLIKE'],
        ])->fetchAll();

        $result = [];
        foreach ($arLike as $like) {

            if ($like['LIKE'] == 'Y') {
                $result['LIKES'][$like['QUESTION_ID']] = true;
            }

            if ($like['DISLIKE'] == 'Y') {
                $result['DISLIKES'][$like['QUESTION_ID']] = true;
            }
        }

        return $result;
    }

    public function editAction()
    {
        $request = Application::getInstance()->getContext()->getRequest()->getValues();

        $IdUser = $this->userId;
        if (
            OptionReviews::getConfig('QUESTIONS_ANONYMOUS', SITE_ID) === 'Y'
            && $request['ANONYMOUS'] == 'Y'
            && OptionReviews::getConfig('QUESTIONS_ANONYMOUS_USER', SITE_ID) === 'Y'
        ) {
            $IdUser = 0;
        }

        $field = [
            'ID_USER' => $IdUser,
            'QUESTION' => $request['QUESTION'],
            'DATE_CHANGE' => new \Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s')
        ];

        $resQuestionsUpdate = Internals\QuestionsTable::update($request['ID'], $field);

        if ($resQuestionsUpdate->isSuccess()) {
            return "SUCCESS";
        }

        $this->errorCollection->add([new Error(Loc::getMessage('SA_REVIEWS_COMPLAINT_ERROR_EDIT_REVIEWS'))]);
        return AjaxJson::createError($this->errorCollection);
    }

    public function likeAction($data)
    {
        $userID = \Bitrix\Main\Engine\CurrentUser::get()->getId();

        if ($data['LIKE'] == 'Y') {
            $data['DISLIKE'] = 'N';
        } elseif ($data['DISLIKE'] == 'Y') {
            $data['LIKE'] = 'N';
        }

        $field = [
            'ID_USER' => $userID,
            'IP' => $_SERVER["REMOTE_ADDR"],
            'DATE_CREATION' => new \Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s'),
            'LIKE' => $data['LIKE'],
            'DISLIKE' => $data['DISLIKE'],
            'ACTIVE' => 'Y',
            'QUESTION_ID' => $data['QUESTION_ID'],
        ];

        try {
            Model\Like::create($field);
        } catch (\Exception $e) {
            $this->errorCollection->add([new Error($e->getMessage())]);
            return AjaxJson::createError($this->errorCollection);
        }
    }
}

?>
