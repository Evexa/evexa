<?

namespace Sotbit\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Sotbit\Reviews\Model\Bans;

Loc::loadMessages(__FILE__);

class BansTable extends Entity\DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getObjectClass()
    {
        return Bans::class;
    }

    public static function getTableName()
    {
        return 'b_sotbit_reviews_bans';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('BANS_ID'),
            )),
            new Entity\IntegerField('ID_USER', array(
                'title' => Loc::getMessage('BANS_ID_USER'),
            )),
            new Entity\StringField('IP', array(
                'title' => Loc::getMessage('BANS_IP'),
                'validation' => function () {
                    return array(
                        new Entity\Validator\RegExp('/^((25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}|())$/')
                    );
                }
            )),
            new Entity\EnumField('TYPE', array(
                'title' => Loc::getMessage('BANS_TYPE'),
                'values' => Bans::getTypeList(),
            )),

            new Entity\IntegerField('ID_MODERATOR', array(
                'title' => Loc::getMessage('BANS_ID_MODERATOR'),
                'default_value' => function () {
                    return  \Bitrix\Main\Engine\CurrentUser::get()->getId();
                },
            )),
            new Entity\DatetimeField('DATE_CREATION', array(
                'title' => Loc::getMessage('BANS_DATE_CREATION'),
                'default_value' => function () {
                    return new DateTime(date(DateTime::getFormat()), DateTime::getFormat());
                },
            )),
            new Entity\DatetimeField('DATE_CHANGE', array(
                'title' => Loc::getMessage('BANS_DATE_CHANGE'),
                'default_value' => function () {
                    return new DateTime(date(DateTime::getFormat()), DateTime::getFormat());
                },
            )),
            new Entity\DatetimeField('DATE_TO', array(
                'title' => Loc::getMessage('BANS_DATE_TO'),
                'default_value' => function () {
                    return new DateTime(date(DateTime::getFormat(), strtotime('+1 year')));
                },
            )),
            new Entity\TextField('REASON', array(
                'title' => Loc::getMessage('BANS_REASON')
            )),
            new Entity\BooleanField('ACTIVE', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('BANS_ACTIVE'),
                'default_value' => 'Y'
            )),
        );
    }
}
