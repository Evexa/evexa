<?

namespace Sotbit\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;
use Sotbit\Reviews\Internals;

Loc::loadMessages(__FILE__);

class AnalyticTable extends Entity\DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_sotbit_reviews_analytic';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('ANALYTIC_ID'),
            )),
            new Entity\IntegerField('ID_USER', array(
                'title' => Loc::getMessage('ANALYTIC_ID_USER'),
            )),
            new Entity\StringField('IP_USER', array(
                'title' => Loc::getMessage('ANALYTIC_IP_USER'),
                'validation' => function () {
                    return array(
                        new Entity\Validator\RegExp('/^((25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])(\.(25[0-5]|2[0-4][0-9]|[0-1][0-9]{2}|[0-9]{2}|[0-9])){3}|())$/')
                    );
                }
            )),
            new Entity\IntegerField('ID_RCQ', array(
                'required' => true,
                'title' => Loc::getMessage('ANALYTIC_ID_RCQ'),
            )),
            new Entity\IntegerField('ACTION', array(
                'required' => true,
                'title' => Loc::getMessage('ANALYTIC_RCQ'),
            )),
            new Entity\DatetimeField('DATE_CREATION', array(
                'title' => Loc::getMessage('ANALYTIC_DATE_CREATION'),
                'default_value' => new DateTime()
            )),
            new Entity\StringField('VALUE', array(
                'title' => Loc::getMessage('ANALYTIC_VALUE')
            )),
            new Entity\IntegerField('REVIEWS_ID'),
            new Entity\IntegerField('QUESTIONS_ID'),
            (new Reference(
                'REVIEWS',
                Internals\ReviewsTable::class,
                Join::on('this.REVIEWS_ID', 'ref.ID')
            ))->configureJoinType('inner'),

            (new Reference(
                'QUESTIONS',
                Internals\QuestionsTable::class,
                Join::on('this.QUESTIONS_ID', 'ref.ID')
            ))->configureJoinType('inner'),
            (new Reference(
                'USER',
                UserTable::class,
                Join::on('this.ID_USER', 'ref.ID')
            ))->configureJoinType('inner'),
        );
    }
}
