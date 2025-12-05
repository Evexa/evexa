<?

namespace Sotbit\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;

Loc::loadMessages(__FILE__);

class QuestionsTable extends ReviewsDataManager
{

    const iModuleID = "sotbit.reviews";

    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_sotbit_reviews_questions';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
            )),
            new Entity\IntegerField('ID_ELEMENT', array(
                'required' => true,
                'title' => Loc::getMessage('QUESTIONS_ID_ELEMENT'),
            )),
            new Entity\StringField('XML_ID_ELEMENT', array(
                'title' => Loc::getMessage('QUESTIONS_XML_ID_ELEMENT'),
            )),
            new Entity\IntegerField('ID_USER', array(
                'required' => true,
                'title' => Loc::getMessage('QUESTIONS_ID_USER')
            )),
            new Entity\TextField('QUESTION', array(
                'required' => true,
                'title' => Loc::getMessage('QUESTIONS_QUESTION')
            )),
            new Entity\TextField('ANSWER', array(
                'title' => Loc::getMessage('QUESTIONS_ANSWER')
            )),
            new Entity\IntegerField('LIKES', array(
                'required' => true,
                'title' => Loc::getMessage('QUESTIONS_LIKES'),
                'default_value' => 0
            )),
            new Entity\IntegerField('DISLIKES', array(
                'required' => true,
                'title' => Loc::getMessage('QUESTIONS_DISLIKES'),
                'default_value' => 0
            )),
            new Entity\DatetimeField('DATE_CREATION', array(
                'title' => Loc::getMessage('QUESTIONS_DATE_CREATION'),
                'default_value' =>  new DateTime()
            )),
            new Entity\DatetimeField('DATE_CHANGE', array(
                'title' => Loc::getMessage('QUESTIONS_DATE_CHANGE'),
                'nullable' => true,
            )),
            new Entity\BooleanField('MODERATED', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('QUESTIONS_MODERATED')
            )),
            new Entity\IntegerField('MODERATED_BY', array(
                'title' => Loc::getMessage('QUESTIONS_MODERATED_BY'),
                'nullable' => true,
            )),
            new Entity\BooleanField('ACTIVE', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('QUESTIONS_ACTIVE')
            )),
            new Entity\StringField('IP_USER', array(
                'title' => Loc::getMessage('BANS_IP_USER'),
                'validation' => function () {
                    return array(
                        new Entity\Validator\RegExp('/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/')
                    );
                }
            )),
            (new Reference(
                'USER',
                \Bitrix\Main\UserTable::class,
                Join::on('this.ID_USER', 'ref.ID')
            ))->configureJoinType('inner'),
        );
    }
}
