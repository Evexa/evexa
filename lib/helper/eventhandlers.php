<?
namespace Sotbit\Reviews\Helper;

class EventHandlers
{
    public static function OnBuildGlobalMenuHandler(&$arGlobalMenu, &$arModuleMenu)
    {
        Menu::getAdminMenu($arGlobalMenu, $arModuleMenu);
    }
}
?>
