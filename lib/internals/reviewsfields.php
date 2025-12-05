<?

namespace Sotbit\Reviews\Internals;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages( __FILE__ );

class ReviewsfieldsTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sotbit_reviews_reviews_fields';
	}

	public static function getMap()
	{
		return array (
				new Entity\IntegerField( 'ID', array (
						'primary' => true,
						'autocomplete' => true 
				) ),
				new Entity\IntegerField( 'SORT', array (
						'required' => true,
						'title' => Loc::getMessage( 'REVIEWS_FIELDS_SORT' ) 
				) ),
				new Entity\StringField( 'NAME', array (
						'required' => true,
						'title' => Loc::getMessage( 'REVIEWS_FIELDS_NAME' ),
						'unique' => true 
				) ),
				new Entity\StringField( 'TITLE', array (
						'required' => true,
						'title' => Loc::getMessage( 'REVIEWS_FIELDS_TITLE' ) 
				) ),
				new Entity\StringField( 'TYPE', array (
						'required' => true,
						'title' => Loc::getMessage( 'REVIEWS_FIELDS_TYPE' ) 
				) ),
				new Entity\TextField( 'SELECT_VALUE', array (
						'title' => Loc::getMessage( 'REVIEWS_SELECT_VALUE' )
				) ),
				new Entity\BooleanField( 'ACTIVE', array (
						'values' => array (
								'N',
								'Y' 
						),
						'title' => Loc::getMessage( 'REVIEWS_FIELDS_ACTIVE' ) 
				) ) 
		);
	}
}
