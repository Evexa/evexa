<?

use Bitrix\Main\Localization\Loc; 
use Sotbit\Reviews\Helper\OptionReviews;

Loc::loadMessages( __FILE__ );

$arJsConfig = array(
	'sotbit.reviews.swiper' => array(
		'js' => '/bitrix/js/sotbit.reviews/swiper/swiper-bundle.min.js',
		'css' => '/bitrix/js/sotbit.reviews/swiper/swiper-bundle.min.css',
		'lang' => '/bitrix/js/sotbit.reviews/swiper/lang/' . LANGUAGE_ID . '/lang.php',
	),
	'sotbit.reviews.common' => array(
		'js' => '/bitrix/js/sotbit.reviews/common/script.js',
	),
	'sotbit.reviews.dropzone' => array(
		'js' => '/bitrix/js/sotbit.reviews/dropzone/dist/dropzone.min.js',
		'css' => '/bitrix/js/sotbit.reviews/dropzone/dist/dist/dropzone.css',
	),
	'sotbit.reviews.simplebar' => array(
		'js' => '/bitrix/js/sotbit.reviews/simplebar/dist/simplebar.min.js',
		'css' => '/bitrix/js/sotbit.reviews/simplebar/dist/simplebar.min.css',
	),
	'sotbit.reviews.sweetalert2' => array(
		'css' => '/bitrix/js/sotbit.reviews/sweetalert2/dist/sweetalert2.min.css',
		'js' => '/bitrix/js/sotbit.reviews/sweetalert2/dist/sweetalert2.min.js',
	),
	'sotbit.reviews.choices' => array(
		'js' => '/bitrix/js/sotbit.reviews/choices.js/choices.min.js',
	),
	'sotbit.smsauth.fslightbox' => array(
		'js' => '/bitrix/js/sotbit.reviews/fslightbox/fslightbox.js',
	),
	'sotbit.analytic' => array(
		'css' => '/bitrix/js/sotbit.reviews/analytic/analytic.css',
		'rel' => array('stylesheet'),
	),
	'sotbit.reviews' => array(
		'rel' => array( 'sotbit.reviews.sweetalert2', 'sotbit.reviews.swiper', 'sotbit.reviews.dropzone','sotbit.reviews.choices', 'sotbit.smsauth.fslightbox', 'sotbit.reviews.common', 'sotbit.reviews.simplebar'),
	),
);

foreach ($arJsConfig as $ext => $arExt) {
	\CJSCore::RegisterExt($ext, $arExt);
}

class CSotbitReviews
{
	private static $DEMO = 0;
	const iModuleID = "sotbit.reviews";

	public function __construct(
	) {
		$this->setDemo();
	}

	private static function setDemo(
	) {
		$DEMOSTATUS = CModule::IncludeModuleEx(self::iModuleID);
		static::$DEMO = $DEMOSTATUS;
	}

	public static function getDemo()
	{
		return !(self::$DEMO == 0 || self::$DEMO == 3);
	}

	public static function ReturnDemo(
	) {
		return self::$DEMO;
	}

	public static function getModuleEnable()
	{
		$enableReviews = OptionReviews::getConfig('ENABLE_REVIEWS', SITE_ID) === "Y";
		$enableQuestions = OptionReviews::getConfig('ENABLE_QUESTIONS', SITE_ID) === "Y";

		if ($enableReviews == "Y" || $enableQuestions == "Y") {
			return true;
		}

		return false;
	}
}
?>
