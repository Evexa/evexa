<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . "/script.js");
?>
<div class="reviews__control d-flex-reviews j-content-between-reviews r-p-4 reviews__control--comment">
    <h5><?= Loc::getMessage('SA_QUESTIONS_ADD_TITLE')?></h5>
    <form name="questions" class="control-add_comment" method="post" data-active="add">
        <input class="input-text-reviews" type="text" name="QUESTION" required
               placeholder="<?= Loc::getMessage('SA_QUESTIONS_ADD_PLACEHOLDER') ?>"
               maxlength="<?= $arParams['TEXTBOX_MAXLENGTH'] ?>">
        <div class="reviews__control--comment--items">
            <input type="submit" class="btn-reviews btn-reviews--main"
                   value="<?= Loc::getMessage('ADD_COMMENT') ?>">
        </div>
    </form>
</div>

<script>
    BX.message({
        'success_title_quest': '<?= Loc::getMessage('SA_QUESTIONS_SUCCESS_TITLE'); ?>',
        'error_input_quest': '<?= Loc::getMessage('AUTH_ERROR_EMPTY'); ?>',
        'success_btn_mess_quest': '<?= Loc::getMessage('SA_QUESTIONS_SUCCESS_TITLE_BTN'); ?>',
        'success_title_moderate_quest': '<?= Loc::getMessage('SA_QUESTIONS_SUCCESS_TITLE_MODERATE'); ?>',
        'success_moderate_text_quest': '<?= Loc::getMessage('SA_QUESTIONS_SUCCESS_TITLE_MODERATE_TEXT'); ?>',
        'auth_register_quest': '<?= Loc::getMessage('AUTH_ERROR'); ?>',
        'title_quite_quest': '<?= Loc::getMessage('TITLE_QUOTE'); ?>',
        'title_origin_quest': '<?= Loc::getMessage('SA_QUESTIONS_ADD_FORM_TITLE'); ?>',
        'not_buy_message_quest': '<?= Loc::getMessage('NOT_BUY_MESSAGE'); ?>',
        'universal_error_quest': '<?= Loc::getMessage('UNIVERSAL_ERROR'); ?>',
        'repeat_message_quest': '<?= Loc::getMessage('REPEAT_MESSAGE'); ?>',
        'repeat_time_message_quest': '<?= Loc::getMessage('REPEAT_TIME_MESSAGE', ['#DATE#' => $arResult['TIME_REPEAT']]); ?>',
    });



   SA_QuestionsAdd.init({
        canAddQuestions: Boolean(<?=$arResult['CAN_ADD_QUESTIONS']?>),
        canAddQuestionsError: '<?=$arResult['CAN_ADD_QUESTIONS_ERROR']?>',
        isAuthUser: '<?= $arResult['QUESTIONS_ANONYMOUS'] == 'N' && !\Bitrix\Main\Engine\CurrentUser::get()->getId() ? 'N' : 'Y'  ?>',
        isBuyProduct: '<?= $arResult['QUESTIONS_BUY'] ?>',
        isAnonymous: '<?= $arResult['QUESTIONS_ANONYMOUS_USER'] ?>',
        isModerate: '<?= $arResult['QUESTIONS_MODERATION'] ?>',
        canRepeat: '<?= $arResult['CAN_REPEAT'] ?? true ?>',
        modRepeat: '<?= $arResult['QUESTIONS_REPEAT'] ?>',
        countRepeat: '<?= $arResult['COUNT_REPEAT'] ?>',
        isTimeRepeat: '<?= $arResult['TIME_REPEAT'] ? 'Y' : 'N'?>',
        signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>',
    });

</script>
