<?
namespace Sotbit\Reviews\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use CHTTP;
use CSotbitReviews;
use Sotbit\Reviews\Internals\ReviewsTable;

Loc::loadMessages(__FILE__);

class HelperComponent
{
    public static function declOfNum($number, $titles)
    {
        $cases = array(2, 0, 1, 1, 1, 2);
        return $number . " " . $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }

    public static function checkModule(): bool
    {
        if (!Loader::includeModule('sotbit.reviews') || CSotbitReviews::getDemo() === 0) {
            if (!defined("ERROR_404")) {
                define("ERROR_404", "Y");
            }

            CHTTP::setStatus("404 Not Found");

            global $APPLICATION;
            if ($APPLICATION->RestartWorkarea()) {
                require(Application::getDocumentRoot() . "/404.php");
                die();
            }
            return false;
        }

        if (!CSotbitReviews::getModuleEnable()) {
            ShowError(Loc::getMessage('SOTBIT_REVIEWS_ERROR_MODULE_LOADER'));
            return false;
        }

        return true;
    }

    public static function checkActive($contex = 'all'): bool
    {
        $site = Application::getInstance()->getContext()->getSite();

        if (!Loader::includeModule('sotbit.reviews')) {
            return true;
        }

        if ($contex == 'all') {
            $activeReview = (OptionReviews::getConfig('ENABLE_REVIEWS', $site ) == 'Y');
            $activeComment = (OptionReviews::getConfig('ENABLE_QUESTIONS', $site ) == 'Y');

            if (!$activeReview && $activeComment) {
                return true;
            }
        } else {
            if (OptionReviews::getConfig($contex, $site) != 'Y') {
                return true;
            }
        }

        return false;
    }

    public static function getArrayRaiting($field, $id, $count): float
    {
        $res = ReviewsTable::getList([
            'filter' => [$field => $id, 'MODERATED' => 'Y', 'ACTIVE' => 'Y'],
            'select' => ['RATING', 'ID_ELEMENT'],
        ])->fetchAll();

        $result = [];
        if (is_array($res)) {
            foreach ($res as $item) {
                $result[$item['RATING']] += 1;
                $rating[] += $item['RATING'];
            }
            krsort($result);
        }
        foreach ($result as $star => $value) {
            $return['AVERAGE_RATING_ITEM'][$star] = round($value / $count * 100) ?: 0;
        }

        $return['AVERAGE_RATING'] = 0;
        if ($count > 0 && is_array($rating)) {
            $return['AVERAGE_RATING'] = round(array_sum($rating) / $count, 1);
        }

        return $return['AVERAGE_RATING'];
    }
}
