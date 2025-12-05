<?

namespace Sotbit\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use CBitrixComponent;
use Sotbit\Reviews\Model\Reviews;

Loc::loadMessages(__FILE__);

class ReviewsTable extends ReviewsDataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getObjectClass()
    {
        return Reviews::class;
    }

    public static function getTableName()
    {
        return 'b_sotbit_reviews_reviews';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            new Entity\IntegerField('ID_ELEMENT', array(
                'title' => Loc::getMessage('REVIEWS_ID_ELEMENT')
            )),
            new Entity\StringField('XML_ID_ELEMENT', array(
                'title' => Loc::getMessage('REVIEWS_XML_ID_ELEMENT')
            )),
            new Entity\IntegerField('ID_USER', array(
                'required' => true,
                'title' => Loc::getMessage('REVIEWS_ID_USER')
            )),
            new Entity\IntegerField('RATING', array(
                'required' => true,
                'title' => Loc::getMessage('REVIEWS_RATING')
            )),
            new Entity\StringField('TITLE', array(
                'title' => Loc::getMessage('REVIEWS_TITLE'),
                'nullable' => true,
            )),
            new Entity\TextField('TEXT', array(
                'required' => true,
                'title' => Loc::getMessage('REVIEWS_TEXT')
            )),
            new Entity\TextField('ANSWER', array(
                'title' => Loc::getMessage('REVIEWS_ANSWER')
            )),
            new Entity\TextField('ADD_FIELDS', array(
                'title' => Loc::getMessage('REVIEWS_ADD_FIELDS')
            )),
            new Entity\IntegerField('LIKES', array(
                'required' => true,
                'title' => Loc::getMessage('REVIEWS_LIKES'),
                'default_value' => 0
            )),
            new Entity\IntegerField('DISLIKES', array(
                'required' => true,
                'title' => Loc::getMessage('REVIEWS_DISLIKES'),
                'default_value' => 0
            )),
            new Entity\DatetimeField('DATE_CREATION', array(
                'title' => Loc::getMessage('REVIEWS_DATE_CREATION'),
                'default_value' => new DateTime()
            )),
            new Entity\DatetimeField('DATE_CHANGE', array(
                'title' => Loc::getMessage('REVIEWS_DATE_CHANGE'),
                'nullable' => true,
            )),
            new Entity\BooleanField('MODERATED', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('REVIEWS_MODERATED')
            )),
            new Entity\IntegerField('MODERATED_BY', array(
                'title' => Loc::getMessage('REVIEWS_MODERATED_BY'),
                'nullable' => true,
            )),
            new Entity\BooleanField('ACTIVE', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('REVIEWS_ACTIVE')
            )),
            new Entity\BooleanField('RECOMMENDATED', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('REVIEWS_RECOMMENDATED')
            )),
            new Entity\BooleanField('ANONYMITY', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('REVIEWS_RECOMMENDATED')
            )),
            new Entity\StringField('IP_USER',
                array(
                    'title' => Loc::getMessage('REVIEWS_BANS_IP_USER'),
                    'validation' => function () {
                        return array(
                            new Entity\Validator\RegExp('/^(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}$/')
                        );
                    }
                )),
            new Entity\IntegerField('SHOWS', array(
                'title' => Loc::getMessage('REVIEWS_SHOWS')
            )),
            new Entity\TextField('FILES', array(
                'title' => Loc::getMessage('REVIEWS_FILES')
            )),
            new Entity\TextField('QUOTE', array(
                'title' => Loc::getMessage('REVIEWS_FILES')
            )),
            new Entity\TextField('ADVANTAGES', array(
                'title' => Loc::getMessage('REVIEWS_FILES')
            )),
            new Entity\TextField('FLAWS', array(
                'title' => Loc::getMessage('REVIEWS_FILES')
            )),
            (new Reference(
                'USER',
                UserTable::class,
                Join::on('this.ID_USER', 'ref.ID')
            ))->configureJoinType('inner'),
        );
    }

    public static function onAfterAdd(Event $event)
    {
        CBitrixComponent::clearComponentCache('sotbit:rvw.rating', SITE_ID !== LANGUAGE_ID ? SITE_ID : "");
    }

    public static function onAfterUpdate(Event $event)
    {
        CBitrixComponent::clearComponentCache('sotbit:rvw.rating', SITE_ID !== LANGUAGE_ID ? SITE_ID : "");
    }

    public static function onAfterDelete(Event $event)
    {
        CBitrixComponent::clearComponentCache('sotbit:rvw.rating', SITE_ID !== LANGUAGE_ID ? SITE_ID : "");
    }
}
