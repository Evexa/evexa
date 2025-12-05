<?

namespace Sotbit\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

use Sotbit\Reviews\Internals;
use Sotbit\Reviews\Bill\Bill;
use Sotbit\Reviews\Analytic\Helper;

Loc::loadMessages(__FILE__);

class LikeTable extends Entity\DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_sotbit_reviews_like';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_ID'),
            )),
            new Entity\IntegerField('ID_USER', array(
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_ID_USER'),
            )),
            new Entity\StringField('IP', array(
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_IP'),
                'validation' => function () {
                    return array(
                        new Entity\Validator\RegExp('/^((25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}|())$/')
                    );
                }
            )),
            new Entity\DatetimeField('DATE_CREATION', array(
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_DATE_CREATION')
            )),
            new Entity\DatetimeField('DATE_CHANGE', array(
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_DATE_CHANGE')
            )),
            new Entity\BooleanField('LIKE', array(
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_LIKE'),
                'values' => array('N', 'Y'),
            )),
            new Entity\BooleanField('DISLIKE', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_DISLIKE')
            )),
            new Entity\BooleanField('ACTIVE', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SA_REVIEWS_LIKE_ACTIVE')
            )),
            new Entity\IntegerField('REVIEW_ID'),
            new Entity\IntegerField('QUESTION_ID'),
            (new Reference(
                'REVIEW',
                Internals\ReviewsTable::class,
                Join::on('this.REVIEW_ID', 'ref.ID')
            ))->configureJoinType('inner'),

            (new Reference(
                'QUESTION',
                Internals\QuestionsTable::class,
                Join::on('this.QUESTION_ID', 'ref.ID')
            ))->configureJoinType('inner'),
        );
    }

    public static function onBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $data = $event->getParameter("fields");

        if ($data['DISLIKE'] == 'Y') {
            $data['LIKE'] = 'N';
        } elseif ($data['LIKE'] == 'Y') {
            $data['DISLIKE'] = 'N';
        }

        if ($data['REVIEW_ID']) {

            $review = Internals\ReviewsTable::getList([
                'filter' => ['ID' => $data['REVIEW_ID']],
                'select' => ['LIKES', 'DISLIKES'],
                'limit' => 1,
            ])->fetch();

            $field = self::calculationLikes($review, $data);
            Internals\ReviewsTable::update($data['REVIEW_ID'], $field);

            $data['QUESTION_ID'] = 0;
        } elseif ($data['QUESTION_ID']) {
            $quest = Internals\QuestionsTable::getList([
                'filter' => ['ID' => $data['QUESTION_ID']],
                'select' => ['LIKES', 'DISLIKES'],
                'limit' => 1,
            ])->fetch();

            $field = self::calculationLikes($quest, $data);
            Internals\QuestionsTable::update($data['QUESTION_ID'], $field);

            $data['REVIEW_ID'] = 0;
        }

        $result->modifyFields($data);
        return $result;
    }

    public static function onAfterAdd(Entity\Event $event)
    {
        $data = $event->getParameter("fields");

        $field = [];

        if ($data['DISLIKE'] == 'Y') {
            $field['DISLIKES'] = 1;
        }
        if ($data['LIKE'] == 'Y') {
            $field['LIKES'] = 1;
        }

        $action = $data['LIKE'] == 'Y' ? 'LIKE' : 'DISLIKE';
        if ($data['REVIEW_ID']) {
            Bill::userMoney($data['ID_USER'], $action, SITE_ID, $data['REVIEW_ID'], 'REVIEWS');
            Internals\ReviewsTable::update($data['REVIEW_ID'], $field);
        }

        if($data['QUESTION_ID']){
            Bill::userMoney($data['ID_USER'], $action, SITE_ID, $data['QUESTION_ID'], 'QUESTIONS');
            Internals\QuestionsTable::update($data['QUESTION_ID'], $field);
        }
    }

    private static function calculationLikes($field, $data)
    {
        $originField = $field;

        $operations = [
          'LIKE',
          'DISLIKE',
        ];

        foreach ($operations as $operation) {
            $pluralOperation = $operation . 'S';
            if ($data[$operation] == 'N') {
                $minusField = $pluralOperation;
                $field[$pluralOperation] = $field[$pluralOperation] - 1;
            } elseif ($data[$operation] == 'Y') {
                $plusField = $pluralOperation;
                $field[$pluralOperation] = $field[$pluralOperation] + 1;
            }
            $field[$pluralOperation] = ($field[$pluralOperation] != abs($field[$pluralOperation])) ? 0 : $field[$pluralOperation];
        }

        $summ = \Sotbit\Reviews\Model\Like::recalculationSumm($field, $originField, $data);

        if ($data['REVIEW_ID'] > 0) {
            $idElement = $data['REVIEW_ID'];
            $entity = 'REVIEWS';
        } elseif ($data['QUESTION_ID'] > 0) {
            $idElement = $data['QUESTION_ID'];
            $entity = 'QUESTIONS';
        }

        Helper::updateAnalytic($minusField, $plusField, $idElement, $summ, $data['ID_USER'], $entity );

        return $field;
    }


}
