<?

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


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('sotbit.reviews');

CBitrixComponent::includeComponentClass("sotbit:rvw.reviews");

class ReviewsList extends Reviews implements Controllerable
{
    public function configureActions()
    {
        return [
            'complaints' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => []
            ],
            'like' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                ],
                'postfilters' => []
            ],
            'edit' => [
                'prefilters' => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
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
            "MAX_RATING",
            "DATE_FORMAT",
            "ID_ELEMENT",
            "AJAX",
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

    private function getResult(): void
    {
        Loader::includeModule('iblock');
        Loader::includeModule('main');

        $request = $this->request->getValues();

        $configID = OptionReviews::getConfig('REVIEWS_ID_ELEMENT', SITE_ID);
        $IDx = ($configID == 'ID_ELEMENT' ? 'ID_ELEMENT' : 'XML_ID_ELEMENT');

        $filter = ['=' . $IDx => $this->arParams['ID_ELEMENT'], '=ACTIVE' => 'Y', '=MODERATED' => 'Y'];
        $limit = $this->arParams['COUNT_PAGE'];

        $this->queryTransformation($request, $sort, $filter);

        $this->getNav($filter, $limit);
        $arReviews = $this->getListReviews($filter, $sort);

        $arUserReview = [];
        $arFileIds = [];
        $reviewsIds = [];

        foreach ($arReviews as $key => $reviewsItem) {
            $reviewsIds[] = $reviewsItem['ID'];
            $arUserReview[$key] = $reviewsItem['ID_USER'];

            $arFile = $reviewsItem['FILES'] = unserialize($reviewsItem['FILES']);
            if (is_array($arFile) && !empty($arFile)) {
                foreach ($arFile as $file) {
                    $arFileIds[] = $file;
                }
            }

            $reviewsItem['DATE_CREATION'] = \CIBlockFormatProperties::DateFormat(
                $this->arParams["DATE_FORMAT"], MakeTimeStamp($reviewsItem['DATE_CREATION'], CSite::GetDateFormat())
            );

            $arResult['REVIEWS'][$key] = $reviewsItem;
        }

        $this->preparingData($arResult, $arFileIds, $arUserReview);

        $arResult['USER_REVIEWS_CNT'] = $this->getCountUserReviews($arUserReview);
        $arResult["CNT_LEFT_PGN"] = 3;
        $arResult["CNT_RIGHT_PGN"] = 3;

        $likeResult = $this->getLikesUser($reviewsIds);
        $arResult['LIKES'] = $likeResult['LIKES'];
        $arResult['DISLIKES'] = $likeResult['DISLIKES'];

        $this->arResult = $arResult;
    }

    public function preparingData(&$arResult, $arFileIds, $arUserReview): void
    {
        if (is_array($arFileIds)) {
            $resFileIds = $this->getFilesReviews($arFileIds);
        }

        if (is_array($arUserReview)) {
            $arUsers = $this->getFieldUsers($arUserReview);
        }

        foreach ($arResult['REVIEWS'] as &$itemReview) {
            if (is_array($itemReview['FILES'])) {
                foreach ($itemReview['FILES'] as $key => $fileID) {
                    if (!empty($resFileIds[$fileID])) {
                        $itemReview['FILES'][$key] = $resFileIds[$fileID];
                    }
                }
            }
            if ($itemReview['ID_USER'] > 0 && is_array($arUsers[$itemReview['ID_USER']])) {
                $itemReview = array_merge($itemReview, $arUsers[$itemReview['ID_USER']]);
            }
        }
    }

    public function getFieldUsers($arUserReview): array
    {
        $resUser = \Bitrix\Main\UserTable::getList([
            'order' => ['TIMESTAMP_X' => 'ASC'],
            'filter' => ['=ID' => $arUserReview],
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

        $arUsers = [];
        while ($arUser = $resUser->fetch()) {
            if ($arUser['PERSONAL_PHOTO_ID'] > 0) {
                $arFileResize = CFile::ResizeImageGet($arUser['PERSONAL_PHOTO_ID'],
                    ['width' => 100, 'height' => 100], BX_RESIZE_IMAGE_EXACT);
                $arUser['PERSONAL_PHOTO_SRC'] = $arFileResize['src'];
            } else {
                $arUser['PERSONAL_PHOTO_SRC'] = OptionReviews::getConfig('REVIEWS_NO_USER_IMAGE');
            }
            $id = $arUser['ID'];
            unset($arUser['ID']);
            $arUsers[$id] = $arUser;
        }

        return $arUsers;
    }

    public function getFilesReviews($arFileIds): array
    {
        $resFile = \Bitrix\Main\FileTable::getList([
            'order' => ['TIMESTAMP_X' => 'ASC'],
            'filter' => ['=ID' => $arFileIds],
            'select' => ['*'],
            'cache' => ['ttl' => 3600, 'cache_joins' => true],
        ]);

        $resFileIds = [];
        while ($arResFile = $resFile->fetch()) {
            $resFileIds[$arResFile['ID']] = $arResFile;
        }

        return $resFileIds;
    }

    public function getListReviews($filter, $sort = ''): array
    {
        $rsData = Internals\ReviewsTable::getList(
            [
                'select' => [
                    '*',
                    'DATE_CREATION_ORIG' => 'DATE_CREATION',
                    'DATE_CREATION',

                ],
                'filter' => $filter,
                'order' => $sort ?: ['DATE_CREATION' => 'desc'],
                'limit' => $this->nav->getLimit(),
                'offset' => $this->nav->getOffset(),

                'cache' => ['ttl' => 3600, 'cache_joins' => true],
            ]);

        return $rsData->fetchAll();
    }

    public function queryTransformation($request, &$sort, &$filter): void
    {
        if (!empty($request['DATE_CREATION'])) {
            $sort['DATE_CREATION'] = $request['DATE_CREATION'];
        }

        if ($request['FILES'] == 'on') {
            $filter['!%FILES'] = 'N;';
        } else {
            $request['FILES'] = 'off';
        }

        $filter['<=RATING'] = $this->arParams["MAX_RATING"];
        if ($request['RATING'] > 0) {
            $filter['RATING'] = $request['RATING'] == '0' ? '' : $request['RATING'];
        }
    }

    public function getNav($filter, $limit): void
    {
        $cnt = Internals\ReviewsTable::getCount($filter);
        $nav = new \Bitrix\Main\UI\PageNavigation("review");
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
        $reviews = Internals\ReviewsTable::getList([
            'select' => ['ID_USER'],
            'filter' => ['=ID_USER' => $arUserID, '=ACTIVE' => 'Y', '=MODERATED' => 'Y'],
            'cache' => ['ttl' => 3600, 'cache_joins' => true],
        ])->fetchAll();
        return array_count_values(array_column($reviews, 'ID_USER'));
    }

    public function getLikesUser($reviewsIds): array
    {
        $arLike = Internals\LikeTable::getList([
            'filter' => [
                "LOGIC" => "OR",
                ["=IP" => $_SERVER["REMOTE_ADDR"], ["REVIEW_ID" => $reviewsIds]],
                ["=ID_USER" => $this->userId, ["REVIEW_ID" => $reviewsIds]],
            ],
            'select' => ['REVIEW_ID', 'LIKE', 'DISLIKE'],
        ])->fetchAll();

        $result = [];
        foreach ($arLike as $like) {

            if ($like['LIKE'] == 'Y') {
                $result['LIKES'][$like['REVIEW_ID']] = true;
            }

            if ($like['DISLIKE'] == 'Y') {
                $result['DISLIKES'][$like['REVIEW_ID']] = true;
            }
        }

        return $result;
    }

    public function complaintsAction($id): ?array
    {
        if (!$id) {
            $this->errorCollection->setError(new Error(Loc::getMessage('SA_REVIEWS_COMPLAINT_ERROR_REVIEW_NOT_FOUND')));
            return null;
        }

        $banObject = new Model\Bans();
        try {
            $banObject->createByReview($id);
            return [
                'status' => 'success',
                'message' => Loc::getMessage('SA_REVIEWS_COMPLAINT_SUCCESS')
            ];
        } catch (\Throwable $exception) {
            $this->errorCollection->setError(new Error($exception->getMessage()));
            return null;
        }
    }

    public function editAction()
    {
        $request = Application::getInstance()->getContext()->getRequest()->getValues();

        $IdUser = $this->userId;
        if (
            OptionReviews::getConfig('REVIEWS_ANONYMOUS', SITE_ID) == 'Y'
            && $request['ANONYMOUS'] == 'Y'
            && OptionReviews::getConfig('REVIEWS_ANONYMOUS_USER', SITE_ID)
            == 'Y'
        ) {
            $IdUser = 0;
        }
        $field = [
            'ID_USER' => $IdUser,
            'TEXT' => $request['COMMENT'],
            'RATING' => $request['RATING'],
            'FILES' => serialize($request['MEDIA']),
            'DATE_CHANGE' => new \Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s')
        ];

        $resReviewsUpdate = Internals\ReviewsTable::update($request['ID'], $field);

        if ($resReviewsUpdate->isSuccess()) {
            return "SUCCESS";
        }

        $errorCollection = new ErrorCollection();
        $errorCollection->add([new Error(Loc::getMessage('SA_REVIEWS_COMPLAINT_ERROR_EDIT_REVIEWS'))]);
        return AjaxJson::createError($errorCollection);
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
            'REVIEW_ID' => $data['REVIEW_ID'],
        ];

        $res = Model\Like::create($field);

        if (!$res->isSuccess()) {
            $this->errorCollection->add([new Error($res->getErrors())]);
            return AjaxJson::createError($this->errorCollection);
        }
    }
}

?>
