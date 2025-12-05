<?
use Sotbit\Reviews\Internals\ReviewsfieldsTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Entity;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type;
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if(!Loader::includeModule( 'sotbit.reviews' ))
	return false;
$POST_RIGHT = $APPLICATION->GetGroupRight( CSotbitReviews::iModuleID );
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm( GetMessage( "ACCESS_DENIED" ) );
IncludeModuleLangFile( __FILE__ );
global $APPLICATION;
$CSotbitReviews = new CSotbitReviews();
if(!$CSotbitReviews->getDemo())
	return false;
$sTableID = "b_sotbit_reviews_reviews_fields";
$oSort = new CAdminSorting( $sTableID, "SORT", "desc" );
$lAdmin = new CAdminList( $sTableID, $oSort );
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
		"find_name",
		"find_title",
		"find_type",
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
	$arFilter['NAME'] = $find_name;
	$arFilter['TITLE'] = $find_title;
	$arFilter['TYPE'] = $find_type;
	$arFilter['ACTIVE'] = $find_active;
	if(empty( $arFilter['ID'] ))
		unset( $arFilter['ID'] );
	if(empty( $arFilter['TITLE'] ))
		unset( $arFilter['TITLE'] );
	if(empty( $arFilter['NAME'] ))
		unset( $arFilter['NAME'] );
	if(empty( $arFilter['TYPE'] ))
		unset( $arFilter['TYPE'] );
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
			foreach( $arFields as $key => $value )
				$arData[$key] = $value;
			$result = ReviewsfieldsTable::update( $ID, $arData );
			unset( $arData );
			if(!$result->isSuccess())
			{
				$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NO_ZAPIS" ), $ID );
			}
			unset( $result );
		}
		else
		{
			$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NO_ZAPIS" ), $ID );
		}
	}
}
if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = ReviewsfieldsTable::getList( array (
				'select' => array (
						'ID',
						'SORT',
						'TITLE',
						'NAME',
						'TYPE',
						'ACTIVE' 
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
				$result = ReviewsfieldsTable::delete( $ID );
				if(!$result->isSuccess())
				{
					$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_DEL_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NO_ZAPIS" ), $ID );
				}
				unset( $result );
				break;
			case "activate" :
			case "deactivate" :
				if($ID>0)
				{
					$arFields["ACTIVE"] = ($_REQUEST['action']=="activate" ? "Y" : "N");
					$result = ReviewsfieldsTable::update( $ID, array (
							'ACTIVE' => $arFields["ACTIVE"] 
					) );
					if(!$result->isSuccess())
						$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NO_ZAPIS" ), $ID );
					unset( $result );
				}
				else
					$lAdmin->AddGroupError( GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_SAVE_ERROR" )." ".GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NO_ZAPIS" ), $ID );
				break;
		}
	}
}
$rsData = ReviewsfieldsTable::getList( array (
		'select' => array (
				'ID',
				'SORT',
				'NAME',
				'TITLE',
				'TYPE',
				'ACTIVE' 
		),
		'filter' => $arFilter,
		'order' => array (
				$by => $order 
		) 
) );
$rsData = new CAdminResult( $rsData, $sTableID );
$rsData->NavStart();
$lAdmin->NavText( $rsData->GetNavPrint( GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NAV" ) ) );
$lAdmin->AddHeaders( array (
		array (
				"id" => "ID",
				"content" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TABLE_ID" ),
				"sort" => "ID",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "SORT",
				"content" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TABLE_SORT" ),
				"sort" => "SORT",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "NAME",
				"content" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TABLE_NAME" ),
				"sort" => "NAME",
				"default" => true 
		),
		array (
				"id" => "TITLE",
				"content" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TABLE_TITLE" ),
				"sort" => "TITLE",
				"default" => true 
		),
		array (
				"id" => "TYPE",
				"content" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TABLE_TYPE" ),
				"sort" => "TYPE",
				"align" => "right",
				"default" => true 
		),
		array (
				"id" => "ACTIVE",
				"content" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TABLE_ACTIVE" ),
				"sort" => "ACTIVE",
				"default" => true 
		) 
) );
while( $arRes = $rsData->NavNext( true, "f_" ) )
{
	$row = & $lAdmin->AddRow( $f_ID, $arRes );
	$row->AddViewField( "ID", '<a href="sotbit.reviews_reviews_fields_edit.php?ID='.$f_ID.'&lang='.LANG.'"">'.$f_ID.'</a>' );
	$row->AddInputField( "SORT", array (
			'value' => $f_SORT 
	) );
	unset( $f_SORT );
	$row->AddInputField( "NAME", array (
			'value' => $f_NAME 
	) );
	unset( $f_NAME );
	$row->AddInputField( "TITLE", array (
			'value' => $f_TITLE 
	) );
	unset( $f_TITLE );
	$row->AddInputField( "TYPE", array (
			'value' => $f_TYPE 
	) );
	unset( $f_TYPE );
	$row->AddCheckField( "ACTIVE" );
	$arActions = Array ();
	$arActions[] = array (
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_EDIT" ),
			"ACTION" => $lAdmin->ActionRedirect( "sotbit.reviews_reviews_fields_edit.php?ID=".$f_ID ) 
	);
	
	if($POST_RIGHT>="W")
		$arActions[] = array (
				"ICON" => "delete",
				"TEXT" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_DEL" ),
				"ACTION" => "if(confirm('".GetMessage( 'REVIEWS_FIELDS_DEL_CONF' )."')) ".$lAdmin->ActionDoGroup( $f_ID, "delete" ) 
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
				"title" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_LIST_SELECTED" ),
				"value" => $rsData->SelectedRowsCount() 
		),
		array (
				"counter" => true,
				"title" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_LIST_CHECKED" ),
				"value" => "0" 
		) 
) );
$lAdmin->AddGroupActionTable( Array (
		"delete" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_LIST_DELETE" ),
		"activate" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_LIST_ACTIVATE" ),
		"deactivate" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_LIST_DEACTIVATE" ) 
) );
$aContext = array (
		array (
				"TEXT" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_POST_ADD_TEXT" ),
				"LINK" => "sotbit.reviews_reviews_fields_edit.php?lang=".LANG,
				"TITLE" => GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_POST_ADD_TITLE" ),
				"ICON" => "btn_new" 
		) 
);
$lAdmin->AddAdminContextMenu( $aContext );
unset( $aContext );
$lAdmin->CheckListMode();
$APPLICATION->SetTitle( GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TITLE" ) );
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$oFilter = new CAdminFilter( $sTableID."_filter", array (
		GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_ID" ),
		GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NAME" ),
		GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TITLE" ),
		GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TYPE" ),
		GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_ACTIVE" ) 
) );
?>
<form name="find_form" method="get"
	action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
		<td><b><?=GetMessage(CSotbitReviews::iModuleID."_REVIEWS_FIELDS_FIND")?>:</b></td>
		<td><input type="text" size="25" name="find"
			value="<?echo htmlspecialchars($find)?>"
			title="<?=GetMessage(CSotbitReviews::iModuleID."_REVIEWS_FIELDS_FIND_TITLE")?>">
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
		<td><?=GetMessage(CSotbitReviews::iModuleID."_REVIEWS_FIELDS_ID")?>:</td>
		<td><input type="text" name="find_id" size="47"
			value="<?echo htmlspecialchars($find_id)?>">
<?
unset( $find_id );
?>
</td>
	</tr>
	<tr>
		<td><?=GetMessage(CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TITLE")?>:</td>
		<td><input type="text" name="find_title" size="47"
			value="<?echo htmlspecialchars($find_title)?>">
<?
unset( $find_title );
?>
</td>
	</tr>
	<tr>
		<td><?=GetMessage(CSotbitReviews::iModuleID."_REVIEWS_FIELDS_NAME")?>:</td>
		<td><input type="text" name="find_name" size="47"
			value="<?echo htmlspecialchars($find_name)?>">
<?
unset( $find_name );
?>
</td>
	</tr>
	<tr>
		<td><?=GetMessage(CSotbitReviews::iModuleID."_REVIEWS_FIELDS_TYPE")?>:</td>
		<td><input type="text" name="find_type" size="47"
			value="<?echo htmlspecialchars($find_type)?>">
<?
unset( $find_type );
?>
</td>
	</tr>
	<tr>
		<td><?=GetMessage(CSotbitReviews::iModuleID."_REVIEWS_FIELDS_ACTIVE")?>:</td>
		<td>
<?
$arr = array (
		"reference" => array (
				GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_POST_YES" ),
				GetMessage( CSotbitReviews::iModuleID."_REVIEWS_FIELDS_POST_NO" ) 
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