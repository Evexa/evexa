<?
namespace Sotbit\Reviews\Helper;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;

class Menu
{
    public static function getAdminMenu(&$arGlobalMenu)
    {
        global $APPLICATION;

        $rsSites = \CSite::GetList($by = "sort", $order = "desc", array("ACTIVE" => "Y"));
        while ($arSite = $rsSites->Fetch()) {
            $Sites[] = $arSite;
        }
        unset($rsSites);
        unset($arSite);

        $Paths = array('reviews' => '_settings.php', 'comments' => '_settings.php', 'questions' => '_settings.php');
        if (count($Sites) == 1) { //If one site
            foreach ($Paths as $key => $Path)
                $Settings[$key] = array(
                    "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_" . $key . "_SETTINGS_TEXT"),
                    "url" => "sotbit.reviews_" . $key . $Path . "?lang=" . LANGUAGE_ID . '&site=' . $Sites[0]['LID'],
                    "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_" . $key . "_SETTINGS_TEXT")
                );
        } else {//If some site
            $Items = array();
            foreach ($Paths as $key => $Path) {
                foreach ($Sites as $Site) {
                    $Items[$key][] = array(
                        "text" => '[' . $Site['LID'] . '] ' . $Site['NAME'],
                        "url" => "sotbit.reviews_" . $key . $Path . "?lang=" . LANGUAGE_ID . '&site=' . $Site['LID'],
                        "title" => $Site['NAME']
                    );
                }
            }
            foreach ($Paths as $key => $Path)
                $Settings[$key] = array(
                    "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_" . $key . "_SETTINGS_TEXT"),
                    "items_id" => "menu_sotbit.reviews_settings" . $key,
                    "items" =>
                        $Items[$key]
                ,
                    "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_" . $key . "_SETTINGS_TEXT")
                );
        }

        if (!isset($arGlobalMenu['global_menu_sotbit'])) {
            $arGlobalMenu['global_menu_sotbit'] = [
                'menu_id' => 'sotbit',
                'text' => Loc::getMessage(
                    \CSotbitReviews::iModuleID . '_GLOBAL_MENU'
                ),
                'title' => Loc::getMessage(
                    \CSotbitReviews::iModuleID . '_GLOBAL_MENU'
                ),
                'sort' => 1000,
                'items_id' => 'global_menu_sotbit_items',
                "icon" => "",
                "page_icon" => "",
            ];
        }

        if ($APPLICATION->GetGroupRight(\CSotbitReviews::iModuleID) === "D") {
            return;
        }

        $aMenu = [
            "parent_menu" => 'global_menu_sotbit',
            "section" => 'sotbit.reviews',
            "sort" => 750,
            "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_TEXT"),
            "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_TITLE"),
            "url" => "sotbit.reviews_reviews_list.php?lang=" . LANGUAGE_ID,
            "icon" => "sotbit_reviews_menu_icon",
            "page_icon" => "sotbit_reviews_page_icon",
            "items_id" => "menu_sotbit.reviews",
            "items" => [
                [
                    "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_REVIEWS_TEXT"),
                    "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_REVIEWS_TITLE"),
                    "dynamic" => true,
                    "items_id" => "menu_sotbit.reviews.reviews",
                    "items" => [
                        [
                            "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_REVIEWS_TEXT_LIST"),
                            "url" => "sotbit.reviews_reviews_list.php?lang=" . LANGUAGE_ID,
                            "more_url" => ["sotbit.reviews_reviews_list.php", "sotbit.reviews_reviews_edit.php"],
                            "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_REVIEWS_TEXT_LIST")
                        ],
                        [
                            "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_REVIEWS_FIELDS_TEXT"),
                            "url" => "sotbit.reviews_reviews_fields_list.php?lang=" . LANGUAGE_ID,
                            "more_url" => ["sotbit.reviews_reviews_fields_list.php", "sotbit.reviews_reviews_fields_edit.php"],
                            "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_REVIEWS_FIELDS_TEXT")
                        ],
                        $Settings['reviews'],
                    ],
                ],
                [
                    "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_COMMENTS_TEXT"),
                    "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_COMMENTS_TITLE"),
                    "dynamic" => true,
                    "items_id" => "menu_sotbit.reviews.questions",
                    "items" => [
                        [
                            "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_COMMENTS_TEXT_LIST"),
                            "url" => "sotbit.reviews_questions_list.php?lang=" . LANGUAGE_ID,
                            "more_url" => ["sotbit.reviews_questions_list.php", "sotbit.reviews_questions_edit.php"],
                            "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_COMMENTS_TEXT_LIST")
                        ],
                        $Settings['questions'],
                        $Settings['questions_analytic']
                    ],
                ],
                [
                    "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_BANS_TEXT"),
                    "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_BANS_TITLE"),
                    "dynamic" => true,
                    "items_id" => "menu_sotbit.reviews.bans",
                    "items" => [
                        [
                            "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_bans_SETTINGS_TEXT"),
                            "url" => "sotbit.reviews_bans_list.php?lang=" . LANGUAGE_ID,
                            "more_url" => ["sotbit.reviews_bans_list.php", "sotbit.reviews_bans_edit.php"],
                            "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_bans_SETTINGS_TEXT")
                        ],
                    ],
                ],
                [
                    "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_ANALYTIC_TEXT"),
                    "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_ANALYTIC_TITLE"),
                    "dynamic" => true,
                    "items_id" => "menu_sotbit.reviews.analytic",
                    "items" => [
                        [
                            "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_reviews_analytic_SETTINGS_TEXT"),
                            "url" => "sotbit.reviews_reviews_analytic.php?lang=" . LANGUAGE_ID,
                            "more_url" => ["sotbit.reviews_reviews_analytic.php", "sotbit.reviews_reviews_analytic.php"],
                            "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_reviews_analytic_SETTINGS_TEXT")
                        ],
                        /* [
                             "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_comments_analytic_SETTINGS_TEXT"),
                             "url" => "sotbit.reviews_comments_analytic.php?lang=" . LANGUAGE_ID,
                             "more_url" => ["sotbit.reviews_comments_analytic.php", "sotbit.reviews_comments_analytic.php"],
                             "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_comments_analytic_SETTINGS_TEXT")
                         ],
                         [
                             "text" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_questions_analytic_SETTINGS_TEXT"),
                             "url" => "sotbit.reviews_questions_analytic.php?lang=" . LANGUAGE_ID,
                             "more_url" => ["sotbit.reviews_questions_analytic.php", "sotbit.reviews_questions_analytic.php"],
                             "title" => Loc::getMessage("SA_REVIEWS_MENU_REVIEWS_questions_analytic_SETTINGS_TEXT")
                         ],*/
                    ]
                ]
            ],
        ];
        $arGlobalMenu['global_menu_sotbit']['items']['sotbit.reviews'] = $aMenu;
    }
}

?>
