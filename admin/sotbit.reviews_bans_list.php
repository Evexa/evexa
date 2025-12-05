<?
use Sotbit\Reviews\Internals\BansTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$id_module = 'sotbit.reviews';
if(!Loader::includeModule( $id_module )||!Loader::includeModule( 'iblock' ))
	return false;

global $APPLICATION;

$POST_RIGHT = $APPLICATION->GetGroupRight( CSotbitReviews::iModuleID );
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );

IncludeModuleLangFile( __FILE__ );

$CSotbitReviews = new CSotbitReviews();
if(!$CSotbitReviews->getDemo())
	return false;

$sTableID = "b_sotbit_reviews_bans_users";

$oSort = new CAdminSorting( $sTableID, "DATE_CHANGE", "desc" );

$lAdmin = new CAdminList( $sTableID, $oSort );

function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach( $FilterArr as $f )
		global $$f;
	return count( $lAdmin->arFilterErrors )==0;
}

$FilterArr = Array (
		"find",
		"find_id",
		"find_id_user",
		"find_active" 
);

$lAdmin->InitFilter( $FilterArr );
$arFilter = array ();

if(CheckFilter())
{
	if($find!=''&&$find_type=='id')
		$arFilter['ID'] = $find;
	elseif($find_id!='')
		$arFilter['ID'] = $find_id;
	$arFilter['ID_USER'] = $find_id_user;
	$arFilter['ACTIVE'] = $find_active;
	if(empty( $arFilter['ID'] ))
		unset( $arFilter['ID'] );
	if(empty( $arFilter['ID_USER'] ))
		unset( $arFilter['ID_USER'] );
	if(empty( $arFilter['ACTIVE'] ))
		unset( $arFilter['ACTIVE'] );
}

if($lAdmin->EditAction())
{
	foreach( $FIELDS as $ID => $arFields )
	{
		if(!$lAdmin->IsUpdated( $ID ))
			continue;
		
		$ID = IntVal( $ID );
		if($ID>0)
		{
			$result = BansTable::update( $ID, $arData );
			unset( $arData );
			if(!$result->isSuccess())
			{
				$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_BANS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_BANS_NO_ZAPIS" ), $ID );
			}
			else
			{

			}
			unset( $result );
		}
		else
		{
			$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_BANS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_BANS_NO_ZAPIS" ), $ID );
		}
	}
}
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = BansTable::getList( array (
				'select' => array (
						'ID',
				),
				'filter' => $arFilter,
				'order' => array (
						$by => $order 
				) 
		) );
		while( $arRes = $rsData->Fetch() )
			$arID[] = $arRes['ID'];
		unset( $arRes );
		unset( $rsData );
	}
	foreach( $arID as $ID )
	{
		if(strlen( $ID )<=0)
			continue;
		$ID = IntVal( $ID );

		switch($_REQUEST['action'])
		{
			case "delete" :
				$result = BansTable::delete( $ID );
				if(!$result->isSuccess())
				{
					$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_BANS_DEL_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_BANS_NO_ZAPIS" ), $ID );
				}
				else
				{

				}
				unset( $result );
				break;
			case "activate" :
			case "deactivate" :
				if($ID>0)
				{
					$arFields["ACTIVE"] = ($_REQUEST['action']=="activate" ? "Y" : "N");
					$result = BansTable::update( $ID, array (
							'ACTIVE' => $arFields["ACTIVE"] 
					) );
					if(!$result->isSuccess())
						$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_BANS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_BANS_NO_ZAPIS" ), $ID );
					else
					{
					}
					unset( $result );
				}
				else
				{
					$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_BANS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_BANS_NO_ZAPIS" ), $ID );
				}
				break;
		}
	}
}

$rsData = BansTable::getList( array(
		'select' => array('ID','ID_USER','IP','DATE_CREATION','DATE_CHANGE','DATE_TO','ACTIVE'),
		'filter' => $arFilter,
		'order' => array($by => $order),
) );

$rsData = new CAdminResult( $rsData, $sTableID );
$rsData->NavStart();
$lAdmin->NavText( $rsData->GetNavPrint( GetMessage( CSotbitReviews::iModuleID."_BANS_NAV" ) ) );
$lAdmin->AddHeaders( array (
		array (
				"id" => "ID",
				"content" => GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_ID" ),
				"sort" => "ID",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "ID_USER",
				"content" => GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_ID_USER" ),
				"sort" => "ID_USER",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "IP",
				"content" => GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_IP" ),
				"sort" => "IP",
				"align" => "right",
				"default" => true
		),
		array (
				"id" => "DATE_CREATION",
				"content" => GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_DATE_CREATION" ),
				"sort" => "DATE_CREATION",
				"default" => true 
		),
		array (
				"id" => "DATE_CHANGE",
				"content" => GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_DATE_CHANGE" ),
				"sort" => "DATE_CHANGE",
				"default" => true
		),
		array (
				"id" => "DATE_TO",
				"content" => GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_DATE_TO" ),
				"sort" => "DATE_TO",
				"default" => true
		),
		array (
				"id" => "ACTIVE",
				"content" => GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_ACTIVE" ),
				"sort" => "ACTIVE",
				"default" => true 
		) 
) );
while( $arRes = $rsData->NavNext( true, "f_" ) )
{
	$row = & $lAdmin->AddRow( $f_ID, $arRes );

	if($f_ID_USER>0)
	{
		$Users = CUser::GetByID( $f_ID_USER );
		if($arItem = $Users->Fetch())
		{
			$row->AddViewField( "ID_USER", '['.$arItem['ID'].'] '.$arItem['LAST_NAME'].' '.$arItem['NAME'] );
		}
		unset( $f_ID_USER );
		unset( $Users );
		unset( $arItem );
	}
	elseif($f_ID_USER==0)
	{
		$row->AddViewField( "ID_USER", GetMessage( CSotbitReviews::iModuleID."_BANS_TABLE_USER_NO_AUTH" ));
	}
	
	$row->AddEditField( "IP", $f_IP );
	unset( $f_IP );
	
	$row->AddViewField( "DATE_CREATION", $f_DATE_CREATION );
	unset( $f_DATE_CREATION );
	$row->AddViewField( "DATE_CHANGE", $f_DATE_CHANGE );
	unset( $f_DATE_CHANGE );
	$row->AddViewField( "DATE_TO", $f_DATE_TO );
	unset( $f_DATE_TO );
	$row->AddCheckField( "ACTIVE" );
	$arActions = Array ();
	$arActions[] = array (
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => GetMessage( CSotbitReviews::iModuleID."_BANS_EDIT" ),
			"ACTION" => $lAdmin->ActionRedirect( "sotbit.reviews_bans_edit.php?ID=".$f_ID ) 
	);
	
	if($POST_RIGHT>="W")
		$arActions[] = array (
				"ICON" => "delete",
				"TEXT" => GetMessage( CSotbitReviews::iModuleID."_BANS_DEL" ),
				"ACTION" => "if(confirm('".GetMessage( CSotbitReviews::iModuleID.'_BANS_DEL_CONF' )."')) ".$lAdmin->ActionDoGroup( $f_ID, "delete" ) 
		);
	$arActions[] = array (
			"SEPARATOR" => true 
	);
	
	if(is_set( $arActions[count( $arActions )-1], "SEPARATOR" ))
		unset( $arActions[count( $arActions )-1] );
	
	$row->AddActions( $arActions );
	unset( $f_ID );
	unset( $arActions );
}

$lAdmin->AddFooter( array (
		array (
				"title" => GetMessage( CSotbitReviews::iModuleID."_BANS_LIST_SELECTED" ),
				"value" => $rsData->SelectedRowsCount() 
		),
		array (
				"counter" => true,
				"title" => GetMessage( CSotbitReviews::iModuleID."_BANS_LIST_CHECKED" ),
				"value" => "0" 
		) 
) );
$Moderation = array (
		"delete" => GetMessage( CSotbitReviews::iModuleID."_BANS_LIST_DELETE" ),
		"activate" => GetMessage( CSotbitReviews::iModuleID."_BANS_LIST_ACTIVATE" ),
		"deactivate" => GetMessage( CSotbitReviews::iModuleID."_BANS_LIST_DEACTIVATE" ) 
);
$lAdmin->AddGroupActionTable( $Moderation );
$aContext = array(
	array(
	"TEXT"=>GetMessage(CSotbitReviews::iModuleID."_BANS_ADD_TEXT"),
	"LINK"=>"sotbit.reviews_bans_edit.php?lang=".LANG,
	"TITLE"=>GetMessage(CSotbitReviews::iModuleID."_BANS_ADD_TITLE"),
	"ICON"=>"btn_new",
	),   
);
$lAdmin->AddAdminContextMenu( $aContext );
unset( $aContext );
$lAdmin->CheckListMode();
$APPLICATION->SetTitle( GetMessage( CSotbitReviews::iModuleID."_BANS_TITLE" ) );
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$Moderation = array (
		GetMessage( CSotbitReviews::iModuleID."_BANS_ID" ),
		GetMessage( CSotbitReviews::iModuleID."_BANS_ID_USER" ),
		GetMessage( CSotbitReviews::iModuleID."_BANS_ACTIVE" ) 
);
$oFilter = new CAdminFilter( $sTableID."_filter", $Moderation );
?>
<form name="find_form" method="get"	action="<?echo $APPLICATION->GetCurPage();?>">

<?$oFilter->Begin();?>
<tr>
	<td>
		<b><?=GetMessage(CSotbitReviews::iModuleID."_BANS_FIND")?>:</b>
	</td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialchars($find)?>" title="<?=GetMessage(CSotbitReviews::iModuleID."_BANS_FIND_TITLE")?>">
<?
$arr = array (
		"reference" => array (
				"ID" 
		),
		"reference_id" => array (
				"id" 
		) 
);
echo SelectBoxFromArray( "find_type", $arr, $find_type, "", "" );
unset( $arr );
unset( $find_type );
?>
</td>
</tr>
	<tr>
		<td><?=GetMessage(CSotbitReviews::iModuleID."_BANS_ID")?>:</td>
		<td><input type="text" name="find_id" size="47"	value="<?echo htmlspecialchars($find_id)?>">
	<?
	unset( $find_id );
	?>
		</td>
	</tr>

	<tr>
		<td><?=GetMessage(CSotbitReviews::iModuleID."_BANS_ID_USER")?>:</td>
		<td><input type="text" name="find_id_user" size="47"
			value="<?echo htmlspecialchars($find_id_user)?>">
		<?
		unset( $find_id_user );
		?>
</td>
	</tr>

	<tr>
		<td><?=GetMessage(CSotbitReviews::iModuleID."_BANS_ACTIVE")?>:</td>
		<td>
<?
$arr = array (
		"reference" => array (
				GetMessage( CSotbitReviews::iModuleID."_BANS_POST_YES" ),
				GetMessage( CSotbitReviews::iModuleID."_BANS_POST_NO" ) 
		),
		"reference_id" => array (
				"Y",
				"N" 
		) 
);
echo SelectBoxFromArray( "find_active", $arr, $find_active, "", "" );
unset( $arr );
unset( $find_active );
?>
</td>
	</tr>
<?
$oFilter->Buttons( array (
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form" 
) );
$oFilter->End();
?>
</form>

<?
if($CSotbitReviews->ReturnDemo()==2)
	CAdminMessage::ShowMessage( array (
			"MESSAGE" => GetMessage( CSotbitReviews::iModuleID."_MODULE_DEMO" ),
			'HTML' => true 
	) );
if($CSotbitReviews->ReturnDemo()==3)
	CAdminMessage::ShowMessage( array (
			"MESSAGE" => GetMessage( CSotbitReviews::iModuleID."_MODULE_DEMO_END" ),
			'HTML' => true 
	) );
$lAdmin->DisplayList();
?>

<?
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>