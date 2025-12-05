<?

use Bitrix\Main\Loader;
use Bitrix\Main\Type;
use Bitrix\Main\Localization\Loc;

use Sotbit\Reviews\Internals\ReviewsTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
if (!Loader::includeModule('sotbit.reviews') || !Loader::includeModule('iblock')) {
    return false;
}

global $APPLICATION;

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight(CSotbitReviews::iModuleID);

if ($POST_RIGHT == "D") {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$CSotbitReviews = new CSotbitReviews();
if (!$CSotbitReviews->getDemo()) {
    return false;
}

function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) {
        global $$f;
    }

    return count($lAdmin->arFilterErrors) == 0;
}

$sTableID = "b_sotbit_reviews_reviews";
$oSort = new CAdminSorting($sTableID, "DATE_CREATION", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);
$FilterArr = [
    "find",
    "find_id",
    "find_id_element",
    "find_xml_id_element",
    "find_id_user",
    "find_rating",
    'find_text',
    "find_moderated",
    "find_active"
];
$lAdmin->InitFilter($FilterArr);
$arFilter = [];

if (CheckFilter()) {
    if (!empty($find) && $find_type == 'id') {
        $arFilter['ID'] = $find;
    } elseif (!empty($find_id)) {
        $arFilter['ID'] = $find_id;
    }

    $arFilter['ID_ELEMENT'] = $find_id_element;
    $arFilter['XML_ID_ELEMENT'] = $find_xml_id_element;
    $arFilter['ID_USER'] = $find_id_user;
    $arFilter['RATING'] = $find_rating;
    $arFilter['%TEXT'] = $find_text;
    $arFilter['MODERATED'] = $find_moderated;
    $arFilter['ACTIVE'] = $find_active;

    $arFilterFieldsCodes = [
        "ID_ELEMENT",
        "XML_ID_ELEMENT",
        "ID_USER",
        "RATING",
        "%TEXT",
        "MODERATED",
        "ACTIVE"
    ];

    foreach ($arFilterFieldsCodes as $arFilterFieldsCode) {
        if (empty($arFilter[$arFilterFieldsCode])) {
            unset($arFilter[$arFilterFieldsCode]);
        }
    }
}

if ($lAdmin->EditAction()) {
    foreach ($FIELDS as $ID => $arFields) {
        if (!$lAdmin->IsUpdated($ID)) {
            continue;
        }

        $ID = IntVal($ID);
        if ($ID > 0) {
            // Get old fields
            $arFieldOld = ReviewsTable::getById($ID);
            $Fields['OLD_FIELDS'] = $arFieldOld->fetch();
            unset($arFieldOld);

            // Get site id
            $el_res = CIBlockElement::GetByID($Fields['OLD_FIELDS']['ID_ELEMENT']);
            if ($el_arr = $el_res->GetNext()) {
                $Fields['SITE'] = $el_arr['LID'];
            }

            foreach ($arFields as $key => $value) {
                $arData[$key] = $value;
            }

            $arData['DATE_CHANGE'] = new Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s');
            $arData['MODERATED_BY'] = \Bitrix\Main\Engine\CurrentUser::get()->getId();
            $Fields['NEW_FIELDS'] = $arData;
            $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnBeforeUpdateReview");
            while ($arEvent = $rsEvents->Fetch()) {
                ExecuteModuleEventEx($arEvent, [$Fields]);
            }

            $result = ReviewsTable::update($ID, $arData);
            unset($arData);
            if (!$result->isSuccess()) {
                $lAdmin->AddGroupError(
                    Loc::getMessage("LIST_REVIEWS_ELEMENT_SAVE_ERROR") . " "
                    . Loc::getMessage("LIST_REVIEWS_ELEMENT_NO_ZAPIS"),
                    $ID
                );
            } else {
                $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnAfterUpdateReview");
                while ($arEvent = $rsEvents->Fetch()) {
                    ExecuteModuleEventEx($arEvent, [$Fields]);
                }
            }

            unset($result);
        } else {
            $lAdmin->AddGroupError(
                Loc::getMessage("LIST_REVIEWS_ELEMENT_SAVE_ERROR") . " "
                . Loc::getMessage("LIST_REVIEWS_ELEMENT_NO_ZAPIS"),
                $ID
            );
        }
    }
}

if ($arID = $lAdmin->GroupAction()) {
    if ($_REQUEST['action_target'] == 'selected') {
        $rsData = ReviewsTable::getList([
            'select' => [
                'ID',
                'ID_ELEMENT',
                'XML_ID_ELEMENT',
                'ID_USER',
                'RATING',
                'DATE_CREATION',
                'TEXT',
                'MODERATED',
                'ACTIVE'
            ],
            'filter' => $arFilter,
            'order' => [
                $by => $order
            ]
        ]);
        while ($arRes = $rsData->Fetch()) {
            $arID[] = $arRes['ID'];
        }
        unset($arRes);
        unset($rsData);
    }

    foreach ($arID as $ID) {
        if (strlen($ID) <= 0) {
            continue;
        }

        $ID = IntVal($ID);

        // Get old fields
        $arFields = ReviewsTable::getById($ID);
        $Fields['OLD_FIELDS'] = $arFields->fetch();
        unset($arFields);

        // Get site id
        $el_res = CIBlockElement::GetByID($Fields['OLD_FIELDS']['ID_ELEMENT']);
        if ($el_arr = $el_res->GetNext()) {
            $Fields['SITE'] = $el_arr['LID'];
        }

        switch ($_REQUEST['action']) {
            case "delete" :
                $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnBeforeDeleteReview");
                while ($arEvent = $rsEvents->Fetch()) {
                    ExecuteModuleEventEx($arEvent, [$Fields]);
                }

                $result = ReviewsTable::delete($ID);
                if (!$result->isSuccess()) {
                    $lAdmin->AddGroupError(
                        Loc::getMessage("LIST_REVIEWS_ELEMENT_DEL_ERROR") . " "
                        . Loc::getMessage("LIST_REVIEWS_ELEMENT_NO_ZAPIS"),
                        $ID
                    );
                } else {
                    $lAdmin->AddActionSuccessMessage(Loc::getMessage('LIST_REVIEWS_ELEMENT_DEL_SUCCESS'));

                    $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnAfterDeleteReview");
                    while ($arEvent = $rsEvents->Fetch()) {
                        ExecuteModuleEventEx($arEvent, [$Fields]);
                    }
                }
                unset($result);
                break;
            case "activate" :
            case "deactivate" :
                if ($ID > 0) {
                    $arFields["ACTIVE"] = ($_REQUEST['action'] == "activate" ? "Y" : "N");
                    $Fields['NEW_FIELDS'] = $arFields;
                    $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnBeforeUpdateReview");
                    while ($arEvent = $rsEvents->Fetch()) {
                        ExecuteModuleEventEx($arEvent, [$Fields]);
                    }

                    $result = ReviewsTable::update(
                        $ID,
                        [
                            'ACTIVE' => $arFields["ACTIVE"]
                        ]
                    );
                    if (!$result->isSuccess()) {
                        $lAdmin->AddGroupError(
                            Loc::getMessage("LIST_REVIEWS_ELEMENT_SAVE_ERROR") . " "
                            . Loc::getMessage("LIST_REVIEWS_ELEMENT_NO_ZAPIS"),
                            $ID
                        );
                    } else {
                        $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnAfterUpdateReview");
                        while ($arEvent = $rsEvents->Fetch()) {
                            ExecuteModuleEventEx($arEvent, [$Fields]);
                        }
                    }

                    unset($result);
                } else {
                    $lAdmin->AddGroupError(
                        Loc::getMessage("LIST_REVIEWS_ELEMENT_SAVE_ERROR") . " "
                        . Loc::getMessage("LIST_REVIEWS_ELEMENT_NO_ZAPIS"),
                        $ID
                    );
                }
                break;
            case "moderate" :
            case "unmoderate" :
                if ($ID > 0) {
                    $ModeratorID = \Bitrix\Main\Engine\CurrentUser::get()->getId();
                    $arFields["MODERATED"] = ($_REQUEST['action'] == "moderate" ? "Y" : "N");
                    $Fields['NEW_FIELDS'] = $arFields;
                    $Fields['NEW_FIELDS']['MODERATED_BY'] = $ModeratorID;
                    $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnBeforeUpdateReview");
                    while ($arEvent = $rsEvents->Fetch()) {
                        ExecuteModuleEventEx($arEvent, [$Fields]);
                    }

                    $result = ReviewsTable::update(
                        $ID,
                        [
                            'MODERATED' => $arFields["MODERATED"],
                            'MODERATED_BY' => $ModeratorID
                        ]
                    );

                    if (!$result->isSuccess()) {
                        $lAdmin->AddGroupError(
                            Loc::getMessage("LIST_REVIEWS_ELEMENT_SAVE_ERROR") . " "
                            . Loc::getMessage("LIST_REVIEWS_ELEMENT_NO_ZAPIS"),
                            $ID
                        );
                    } else {
                        $rsEvents = GetModuleEvents(CSotbitReviews::iModuleID, "OnAfterUpdateReview");
                        while ($arEvent = $rsEvents->Fetch()) {
                            ExecuteModuleEventEx($arEvent, [$Fields]);
                        }
                    }
                    unset($result);
                } else {
                    $lAdmin->AddGroupError(
                        Loc::getMessage("LIST_REVIEWS_ELEMENT_SAVE_ERROR") . " "
                        . Loc::getMessage("LIST_REVIEWS_ELEMENT_NO_ZAPIS"),
                        $ID
                    );
                }
                break;
        }
    }
}

$rsData = ReviewsTable::getList([
    'select' => [
        'ID',
        'ID_ELEMENT',
        'XML_ID_ELEMENT',
        'ID_USER',
        'RATING',
        'DATE_CREATION',
        'TEXT',
        'MODERATED',
        'ACTIVE'
    ],
    'filter' => $arFilter,
    'order' => [
        $by => $order
    ]
]);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("LIST_REVIEWS_ELEMENT_NAV")));
$lAdmin->AddHeaders([
    [
        "id" => "ID",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_ID"),
        "sort" => "ID",
        "align" => "right",
        "default" => true
    ],
    [
        "id" => "ID_ELEMENT",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_ID_ELEMENT"),
        "sort" => "ID_ELEMENT",
        "default" => true
    ],
    [
        "id" => "XML_ID_ELEMENT",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_XML_ID_ELEMENT"),
        "sort" => "XML_ID_ELEMENT",
        "default" => true
    ],
    [
        "id" => "ID_USER",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_ID_USER"),
        "sort" => "ID_USER",
        "align" => "right",
        "default" => true
    ],
    [
        "id" => "RATING",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_RATING"),
        "sort" => "RATING",
        "align" => "right",
        "default" => true
    ],
    [
        "id" => "DATE_CREATION",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_DATE_CREATION"),
        "sort" => "DATE_CREATION",
        "default" => true
    ],
    [
        "id" => "TEXT",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_TEXT"),
        "sort" => "TEXT",
        "default" => true
    ],
    [
        "id" => "MODERATED",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_MODERATED"),
        "sort" => "MODERATED",
        "default" => true
    ],
    [
        "id" => "ACTIVE",
        "content" => Loc::getMessage("LIST_REVIEWS_ELEMENT_TABLE_ACTIVE"),
        "sort" => "ACTIVE",
        "default" => true
    ]
]);

while ($arRes = $rsData->NavNext(true, "f_")) {
    $row = &$lAdmin->AddRow($f_ID, $arRes);
    $el_res = CIBlockElement::GetByID($f_ID_ELEMENT);

    if ($el_arr = $el_res->GetNext()) {
        $row->AddViewField(
            "ID_ELEMENT",
            '<a href="sotbit.reviews_reviews_edit.php?ID=' . $f_ID . '&lang=' . LANG . '"">[' . $el_arr['ID'] . '] ' . $el_arr['NAME'] . '</a>'
        );
    }
    unset($el_res);
    unset($el_arr);
    $row->AddViewField("XML_ID_ELEMENT", $f_XML_ID_ELEMENT);

    $Users = CUser::GetByID($f_ID_USER);
    if ($arItem = $Users->Fetch()) {
        $row->AddViewField("ID_USER", '[' . $arItem['ID'] . '] ' . $arItem['LAST_NAME'] . ' ' . $arItem['NAME']);
    } elseif ((int)$f_ID_USER === 0) {
        $row->AddViewField("ID_USER", Loc::getMessage('LIST_REVIEWS_ELEMENT_NO_AUTH_USER'));
    }
    unset($f_ID_USER);
    unset($Users);
    unset($arItem);
    $Rating = '';

    for ($i = 1; $i <= $f_RATING; ++$i) {
        $Rating .= '&#9733;';
    }
    unset($f_RATING);
    $row->AddField("RATING", $Rating);
    unset($Rating);
    $row->AddViewField("DATE_CREATION", $f_DATE_CREATION);
    unset($f_DATE_CREATION);
    $row->AddViewField("TEXT", $f_TEXT);
    unset($f_TEXT);
    $row->AddCheckField(
        "MODERATED",
        ($f_MODERATED == 'Y')
            ? Loc::getMessage('LIST_REVIEWS_ELEMENT_POST_YES')
            : Loc::getMessage('LIST_REVIEWS_ELEMENT_POST_NO')
    );

    $row->AddCheckField("ACTIVE");
    $arActions = [];
    $arActions[] = [
        "ICON" => "edit",
        "DEFAULT" => true,
        "TEXT" => Loc::getMessage("LIST_REVIEWS_ELEMENT_EDIT"),
        "ACTION" => $lAdmin->ActionRedirect("sotbit.reviews_reviews_edit.php?ID=" . $f_ID)
    ];

    if ($POST_RIGHT >= "W") {
        $arActions[] = [
            "ICON" => "delete",
            "TEXT" => Loc::getMessage("LIST_REVIEWS_ELEMENT_DEL"),
            "ACTION" => "if(confirm('" . Loc::getMessage('LIST_REVIEWS_ELEMENT_DEL_CONF') . "')) " . $lAdmin->ActionDoGroup($f_ID,
                    "delete")
        ];
    }
    $arActions[] = [
        "SEPARATOR" => true
    ];

    if (is_set($arActions[count($arActions) - 1], "SEPARATOR")) {
        unset($arActions[count($arActions) - 1]);
    }
    $row->AddActions($arActions);
    unset($f_ID);
    unset($arActions);
}

$lAdmin->AddFooter([
    [
        "title" => Loc::getMessage("LIST_REVIEWS_ELEMENT_LIST_SELECTED"),
        "value" => $rsData->SelectedRowsCount()
    ],
    [
        "counter" => true,
        "title" => Loc::getMessage("LIST_REVIEWS_ELEMENT_LIST_CHECKED"),
        "value" => "0"
    ]
]);
$Moderation = [
    "delete" => Loc::getMessage("LIST_REVIEWS_ELEMENT_LIST_DELETE"),
    "activate" => Loc::getMessage("LIST_REVIEWS_ELEMENT_LIST_ACTIVATE"),
    "deactivate" => Loc::getMessage("LIST_REVIEWS_ELEMENT_LIST_DEACTIVATE")
];
$Moderation = array_merge(
    $Moderation,
    [
        "moderate" => Loc::getMessage("LIST_REVIEWS_ELEMENT_LIST_MODERATE"),
        "unmoderate" => Loc::getMessage("LIST_REVIEWS_ELEMENT_LIST_UNMODERATE")
    ]
);

$lAdmin->AddGroupActionTable($Moderation);
$aContext = [];
$lAdmin->AddAdminContextMenu($aContext);
unset($aContext);
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("LIST_REVIEWS_ELEMENT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
$Moderation = [
    Loc::getMessage("LIST_REVIEWS_ELEMENT_ID"),
    Loc::getMessage("LIST_REVIEWS_ELEMENT_ID_ELEMENT"),
    Loc::getMessage("LIST_REVIEWS_ELEMENT_XML_ID_ELEMENT"),
    Loc::getMessage("LIST_REVIEWS_ELEMENT_ID_USER"),
    Loc::getMessage("LIST_REVIEWS_ELEMENT_RATING"),
    Loc::getMessage("LIST_REVIEWS_ELEMENT_TEXT"),
    Loc::getMessage("LIST_REVIEWS_ELEMENT_MODERATED"),
    Loc::getMessage("LIST_REVIEWS_ELEMENT_ACTIVE")
];

$oFilter = new CAdminFilter($sTableID . "_filter", $Moderation);
?>
    <form name="find_form" method="get"
          action="<?= $APPLICATION->GetCurPage(); ?>">
        <? $oFilter->Begin(); ?>
        <tr>
            <td><b><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_FIND") ?>:</b></td>
            <td><input type="text" size="25" name="find"
                       value="<?= htmlspecialchars($find) ?>"
                       title="<?= Loc::getMessage("LIST_REVIEWS_ELEMENT_FIND_TITLE") ?>">
                <?
                $arr = [
                    "reference" => [
                        "ID"
                    ],
                    "reference_id" => [
                        "id"
                    ]
                ];
                echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
                unset($arr);
                unset($find_type);
                ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_ID") ?>:</td>
            <td><input type="text" name="find_id" size="47"
                       value="<?= htmlspecialchars($find_id) ?>">
                <?
                unset($find_id);
                ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_ID_ELEMENT") ?>:</td>
            <td><input type="text" name="find_id_element" size="47"
                       value="<?= htmlspecialchars($find_id_element) ?>">
                <?
                unset($find_id_element);
                ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_XML_ID_ELEMENT") ?>:</td>
            <td><input type="text" name="find_xml_id_element" size="47"
                       value="<?= htmlspecialchars($find_xml_id_element) ?>">
                <?
                unset($find_xml_id_element);
                ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_ID_USER") ?>:</td>
            <td><input type="text" name="find_id_user" size="47"
                       value="<?= htmlspecialchars($find_id_user) ?>">
                <?
                unset($find_id_user);
                ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_RATING") ?>:</td>
            <td><input type="text" name="find_rating" size="47"
                       value="<?= htmlspecialchars($find_rating) ?>">
                <?
                unset($find_rating);
                ?>
            </td>
        </tr>

        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_TEXT") ?>:</td>
            <td><input type="text" name="find_text" size="47"
                       value="<?= htmlspecialchars($find_text) ?>">
                <?
                unset($find_text);
                ?>
            </td>
        </tr>
        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_MODERATED") ?>:</td>
            <td>
                <?
                $arr = [
                    "reference" => [
                        Loc::getMessage("LIST_REVIEWS_ELEMENT_POST_YES"),
                        Loc::getMessage("LIST_REVIEWS_ELEMENT_POST_NO")
                    ],
                    "reference_id" => [
                        "Y",
                        "N"
                    ]
                ];
                echo SelectBoxFromArray("find_moderated", $arr, $find_moderated, "", "");
                unset($arr);
                unset($find_moderated);
                ?>
            </td>
        </tr>

        <tr>
            <td><?= Loc::getMessage("LIST_REVIEWS_ELEMENT_ACTIVE") ?>:</td>
            <td>
                <?
                $arr = [
                    "reference" => [
                        Loc::getMessage("LIST_REVIEWS_ELEMENT_POST_YES"),
                        Loc::getMessage("LIST_REVIEWS_ELEMENT_POST_NO")
                    ],
                    "reference_id" => [
                        "Y",
                        "N"
                    ]
                ];
                echo SelectBoxFromArray("find_active", $arr, $find_active, "", "");
                unset($arr);
                unset($find_active);
                ?>
            </td>
        </tr>
        <?
        $oFilter->Buttons([
            "table_id" => $sTableID,
            "url" => $APPLICATION->GetCurPage(),
            "form" => "find_form"
        ]);
        $oFilter->End();
        ?>
    </form>
<?
if ($CSotbitReviews->ReturnDemo() == 2) {
    CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage("LIST_MODULE_DEMO"), 'HTML' => true]);
}
if ($CSotbitReviews->ReturnDemo() == 3) {
    CAdminMessage::ShowMessage(["MESSAGE" => Loc::getMessage("LIST_MODULE_DEMO_END"), 'HTML' => true]);
}
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>
