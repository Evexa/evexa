<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

CJSCore::Init(['sotbit.smsauth.fslightbox', 'sotbit.reviews.sweetalert2']);
?>
<div class="reviews" id="reviews">
    <?php
    $APPLICATION->IncludeComponent(
        "sotbit:rvw.statistics",
        "",
        array(
            'MAX_RATING' => $arParams['MAX_RATING'],
            'ID_ELEMENT' => $arParams['ID_ELEMENT'],
            'USER_AGREEMENT_ID' => $arParams['USER_AGREEMENT_ID']
        ),
        $component
    );
    ?>

    <div class="reviews__wrapper">
        <?php
        $APPLICATION->IncludeComponent(
            "sotbit:rvw.questions.add",
            "",
            array(
                'ID_ELEMENT' => $arParams['ID_ELEMENT'],
                'TEXTBOX_MAXLENGTH' => $arParams['TEXTBOX_MAXLENGTH'],
            ),
            $component
        );
        ?>
        <?php
        $APPLICATION->IncludeComponent(
            "sotbit:rvw.questions.list",
            "",
            array(
                'COUNT_PAGE' => $arParams['COUNT_PAGE'],
                'ID_ELEMENT' => $arParams['ID_ELEMENT'],
                'DATE_FORMAT' => $arParams['DATE_FORMAT'],
            ),
            $component
        );
        ?>
    </div>
</div>
<script>
    SA_Questions.init({
        signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>'
    });
</script>
