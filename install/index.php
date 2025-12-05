<?php

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;
use  Bitrix\Main\Loader;
use Sotbit\Reviews\Helper\OptionReviews;
use Sotbit\Reviews\Internals;

Loc::loadMessages(__FILE__);


class sotbit_reviews extends CModule
{
    const MODULE_ID = 'sotbit.reviews';
    var $MODULE_ID = 'sotbit.reviews';

    public $tables;
    public $mailEvents;
    public $arSite;

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__ . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("sotbit.reviews_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("sotbit.reviews_MODULE_DESC");
        $this->PARTNER_NAME = GetMessage("sotbit.reviews_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("sotbit.reviews_PARTNER_URI");
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = ' Y';

        $this->tables = [
            Internals\AnalyticTable::class,
            Internals\BansTable::class,
            Internals\QuestionsTable::class,
            Internals\ReviewsTable::class,
            Internals\ReviewsfieldsTable::class,
            Internals\LikeTable::class,
        ];

        $this->mailEvents = [
            [
                'EVENT_NAME' => 'SOTBIT_REVIEWS_ADD_MAILING_EVENT_SEND',
                'NAME' => Loc::getMessage('EVENT_MAIL_NAME'),
                'MAIL_TEMPLATE' => [
                    'EVENT_NAME' => 'SOTBIT_REVIEWS_ADD_MAILING_EVENT_SEND',
                    'SUBJECT' => Loc::getMessage('EVENT_MAIL_SUBJECT'),
                    'MESSAGE' => Loc::getMessage('EVENT_MAIL_MESSAGE')
                ]
            ],
            [
                'EVENT_NAME' => 'SOTBIT_REVIEWS_ADD_NOTICE_MAILING_EVENT_SEND',
                'NAME' => Loc::getMessage('EVENT_NOTICE_MAIL_NAME'),
                'MAIL_TEMPLATE' => [
                    'EVENT_NAME' => 'SOTBIT_REVIEWS_ADD_NOTICE_MAILING_EVENT_SEND',
                    'SUBJECT' => Loc::getMessage('EVENT_NOTICE_MAIL_SUBJECT'),
                    'MESSAGE' => Loc::getMessage('EVENT_NOTICE_MAIL_MESSAGE'),
                ]
            ]
        ];

        $this->arSite = $this->getSites();
    }

    function InstallEvents()
    {
        // Create type of mail event
        foreach ($this->mailEvents as $itemEventType) {

            $obEventType = new CEventType();
            $obEventType->Add([
                "EVENT_NAME" => $itemEventType["EVENT_NAME"],
                "NAME" => $itemEventType["NAME"],
                "LID" => LANGUAGE_ID,
                "DESCRIPTION" => ""
            ]);

            $obEventMessage = new CEventMessage();
            $obEventMessage->Add(array(
                'ACTIVE' => 'Y',
                'EVENT_NAME' => $itemEventType["EVENT_NAME"],
                'LID' => $this->arSite,
                'EMAIL_FROM' => '#EMAIL_FROM#',
                'EMAIL_TO' => '#EMAIL_TO#',
                'SUBJECT' => $itemEventType['MAIL_TEMPLATE']['SUBJECT'],
                'MESSAGE' => $itemEventType['MAIL_TEMPLATE']['MESSAGE'],
                'BODY_TYPE' => 'html'
            ));
        }

        EventManager::getInstance()->registerEventHandler("main", "OnBuildGlobalMenu", self::MODULE_ID, 'Sotbit\Reviews\Helper\EventHandlers', 'OnBuildGlobalMenuHandler');

        RegisterModuleDependences(self::MODULE_ID, "OnBeforeAddReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnBeforeAddReview");
        RegisterModuleDependences(self::MODULE_ID, "OnAfterAddReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnAfterAddReview");
        RegisterModuleDependences(self::MODULE_ID, "OnBeforeUpdateReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnBeforeUpdateReview");
        RegisterModuleDependences(self::MODULE_ID, "OnAfterUpdateReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnAfterUpdateReview");
        RegisterModuleDependences(self::MODULE_ID, "OnBeforeDeleteReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnBeforeDeleteReview");
        RegisterModuleDependences(self::MODULE_ID, "OnAfterDeleteReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnAfterDeleteReview");

        RegisterModuleDependences(self::MODULE_ID, "OnBeforeAddQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnBeforeAddQuestion");
        RegisterModuleDependences(self::MODULE_ID, "OnAfterAddQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnAfterAddQuestion");
        RegisterModuleDependences(self::MODULE_ID, "OnBeforeUpdateQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnBeforeUpdateQuestion");
        RegisterModuleDependences(self::MODULE_ID, "OnAfterUpdateQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnAfterUpdateQuestion");
        RegisterModuleDependences(self::MODULE_ID, "OnBeforeDeleteQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnBeforeDeleteQuestion");
        RegisterModuleDependences(self::MODULE_ID, "OnAfterDeleteQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnAfterDeleteQuestion");

        RegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", self::MODULE_ID, "Sotbit\Reviews\Event\Product", "OnBeforeElementDelete");
        return true;
    }

    function UnInstallEvents()
    {
        CAgent::RemoveModuleAgents(self::MODULE_ID);

        foreach ($this->mailEvents as $itemEventType) {
            foreach ($this->arSite as $site) {
                $rsMess = CEventMessage::GetList($by = $site, $order = "desc", [
                    'ACTIVE' => 'Y',
                    'EVENT_NAME' => $itemEventType['MAIL_TEMPLATE']['EVENT_NAME'],
                ]);

                if ($arMess = $rsMess->fetch()) {
                    $oEventMessage = new CEventMessage();
                    $res = $oEventMessage->Delete(intval($arMess['ID']));
                }
                $obEventType = new CEventType();
                $obEventType->Delete($itemEventType['EVENT_NAME']);
            }
        }

        EventManager::getInstance()->unRegisterEventHandler("main", "OnBuildGlobalMenu", 'sotbit.reviews', 'Sotbit\Reviews\Helper\EventHandlers', 'OnBuildGlobalMenuHandler');

        UnRegisterModuleDependences(self::MODULE_ID, "OnBeforeAddReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnBeforeAddReview");
        UnRegisterModuleDependences(self::MODULE_ID, "OnAfterAddReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnAfterAddReview");
        UnRegisterModuleDependences(self::MODULE_ID, "OnBeforeUpdateReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnBeforeUpdateReview");
        UnRegisterModuleDependences(self::MODULE_ID, "OnAfterUpdateReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnAfterUpdateReview");
        UnRegisterModuleDependences(self::MODULE_ID, "OnBeforeDeleteReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnBeforeDeleteReview");
        UnRegisterModuleDependences(self::MODULE_ID, "OnAfterDeleteReview", self::MODULE_ID, "Sotbit\Reviews\Event\ReviewEvent", "OnAfterDeleteReview");

        UnRegisterModuleDependences(self::MODULE_ID, "OnBeforeAddQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnBeforeAddQuestion");
        UnRegisterModuleDependences(self::MODULE_ID, "OnAfterAddQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnAfterAddQuestion");
        UnRegisterModuleDependences(self::MODULE_ID, "OnBeforeUpdateQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnBeforeUpdateQuestion");
        UnRegisterModuleDependences(self::MODULE_ID, "OnAfterUpdateQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnAfterUpdateQuestion");
        UnRegisterModuleDependences(self::MODULE_ID, "OnBeforeDeleteQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnBeforeDeleteQuestion");
        UnRegisterModuleDependences(self::MODULE_ID, "OnAfterDeleteQuestion", self::MODULE_ID, "Sotbit\Reviews\Event\QuestionEvent", "OnAfterDeleteQuestion");

        UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", self::MODULE_ID, "Sotbit\Reviews\Event\Product", "OnBeforeElementDelete");

        return true;
    }

    function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/themes/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/", true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true);
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p . '/' . $item, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/' . $item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p . '/' . $item, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/' . $item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }

        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/templates/.default/components/')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p . '/' . $item, $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/.default/components/' . $item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/images', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images/', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/js', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/', true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/admin', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin');
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . self::MODULE_ID . "/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default");
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/themes/.default/')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p . '/' . $item))
                        continue;
                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.')
                            continue;
                        DeleteDirFilesEx('/bitrix/themes/.default/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p . '/' . $item))
                        continue;
                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.')
                            continue;
                        DeleteDirFilesEx('/bitrix/components/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/templates/.default/components/bitrix/')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p . '/' . $item))
                        continue;
                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.')
                            continue;
                        DeleteDirFilesEx('/bitrix/templates/.default/components/bitrix/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }
        return true;
    }

    function InstallDB()
    {
        if (Loader::includeModule(self::MODULE_ID)) {
            $connection = Application::getConnection();
            foreach ($this->tables as $class) {
                $classEntity = $class::getEntity();
                if (!$connection->isTableExists($classEntity->getDBTableName())) {
                    $classEntity->createDbTable();
                }
            }
        }

        return true;
    }

    function InstallSotbitInfo()
    {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/update_client_partner.php");
        $modulesPathDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sotbit.info/";
        if (!file_exists($modulesPathDir)) {
            $strError = '';
            CUpdateClientPartner::LoadModuleNoDemand("sotbit.info", $strError, 'Y', false);
        }
        $module_status = CModule::IncludeModuleEx("sotbit.info");
        if ($module_status == 2 || $module_status == 0 || $module_status == 3) {

            $obModule = CModule::CreateModuleObject("sotbit.info");
            if (is_object($obModule) && !$obModule->IsInstalled()) {
                $obModule->DoInstall();
            }
        }
    }

    function UnInstallDB()
    {
        if (Loader::includeModule(self::MODULE_ID)) {
            $connection = Application::getConnection();

            foreach ($this->tables as $ormClass) {
                $classEntity = $ormClass::getEntity();
                if ($connection->isTableExists($classEntity->getDBTableName())) {
                    $connection->dropTable($classEntity->getDBTableName());
                }
            }
        }

        return true;
    }

    function DoInstall()
    {
        global $APPLICATION;

        $this->InstallFiles();

        if ($_REQUEST['step'] == 1) {
            if ($_SERVER['SERVER_NAME']) {
                $site = $_SERVER['SERVER_NAME'];
            } elseif ($_SERVER['HTTP_HOST']) {
                $site = $_SERVER['HTTP_HOST'];
            }

            $request = array(
                'ACTION' => 'ADD',
                'KEY' => md5("BITRIX" . \Bitrix\Main\Application::getInstance()->getLicense()->getKey() . "LICENCE"),
                'MODULE' => self::MODULE_ID,
                'NAME' => $_REQUEST['Name'],
                'EMAIL' => $_REQUEST['Email'],
                'PHONE' => $_REQUEST['Phone'],
                'SITE' => $site,
            );

            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type: application/json; charset=utf-8\r\n",
                    'content' => json_encode($request)
                )
            );

            $context = stream_context_create($options);
            $answer = file_get_contents('https://www.sotbit.ru:443/api/datacollection/index.php', 0, $context);
            ModuleManager::registerModule(self::MODULE_ID);
            $this->InstallDB();

            if (\Bitrix\Main\Loader::includeModule(self::MODULE_ID)) {
                $this->InstallEvents();
                $this->InstallSotbitInfo();
                OptionReviews::setDefault();
            }

        } else {
            $APPLICATION->IncludeAdminFile(GetMessage("INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sotbit.reviews/install/step.php");
        }

    }

    function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();
        Option::delete(self::MODULE_ID);
        ModuleManager::unRegisterModule(self::MODULE_ID);
    }

    function getSites()
    {
        $result = [];

        $resSite = \Bitrix\Main\SiteTable::getList([
            'filter' => ["ACTIVE" => "Y"],
            'select' => ['LID'],
            'order' => ['SORT' => 'DESC']
        ]);

        while ($arSite = $resSite->fetch()) {
            $result[] = $arSite['LID'];
        }

        return $result;
    }
}
?>
