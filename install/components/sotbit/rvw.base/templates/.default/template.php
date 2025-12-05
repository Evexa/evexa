<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Localization\Loc;
use Sotbit\Reviews\Helper\HelperComponent;

if (\Bitrix\Main\ModuleManager::isModuleInstalled('sotbit.b2c') && SITE_TEMPLATE_ID === 'sotbit_b2c') {
    CJSCore::Init(array(
        "sotbit.b2c.swiper",
        "sotbit.b2c.choices",
        "sotbit.b2c.simplebar",
        "sotbit.b2c.sweetalert2",
        "sotbit.b2c.dropzone",
    ));

    $this->addExternalJs('/bitrix/js/sotbit.reviews/common/script.js');
}

$config = \Sotbit\Reviews\Helper\OptionReviews::getConfigs(SITE_ID);
$enableReview = $config['ENABLE_REVIEWS'] == 'Y';
$enableQuestion = $config['ENABLE_QUESTIONS'] == 'Y';

if ($arParams["SHOW_REVIEWS"] == 'Y' && $arParams["SHOW_QUESTIONS"] == 'Y' && $enableReview && $enableQuestion){
$tab = \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->get('tab');
?>
<div class="reviews-base">
    <div class="reviews reviews-tab-wrap">
        <div class="d-flex-reviews r-p-1">
            <div class="tab-reviews">
                <button class="btn-reviews <?= empty($tab) || $tab == 'reviews' ? 'btn-reviews--active__tab' : '' ?> d-flex-reviews "
                        data-type="reviews">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.7878 3.10215C11.283 2.09877 12.7138 2.09876 13.209 3.10215L15.567 7.87987L20.8395 8.64601C21.9468 8.80691 22.3889 10.1677 21.5877 10.9487L17.7724 14.6676L18.6731 19.9189C18.8622 21.0217 17.7047 21.8627 16.7143 21.342L11.9984 18.8627L7.28252 21.342C6.29213 21.8627 5.13459 21.0217 5.32374 19.9189L6.2244 14.6676L2.40916 10.9487C1.60791 10.1677 2.05005 8.80691 3.15735 8.64601L8.42988 7.87987L10.7878 3.10215ZM11.9984 4.03854L9.74008 8.61443C9.54344 9.01288 9.16332 9.28904 8.72361 9.35294L3.67382 10.0867L7.32788 13.6486C7.64606 13.9587 7.79125 14.4055 7.71614 14.8435L6.85353 19.8729L11.3702 17.4983C11.7635 17.2915 12.2333 17.2915 12.6266 17.4983L17.1433 19.8729L16.2807 14.8435C16.2056 14.4055 16.3508 13.9587 16.6689 13.6486L20.323 10.0867L15.2732 9.35294C14.8335 9.28904 14.4534 9.01288 14.2568 8.61443L11.9984 4.03854Z"
                              fill="#2C2C2C"/>
                    </svg>
                    <?= Loc::getMessage('SA_REVIEWS_TITLE') ?>
                </button>
            </div>
            <div class="tab-reviews">
                <button class="btn-reviews <?= $tab == 'questions' ? 'btn-reviews--active__tab' : '' ?> d-flex-reviews"
                        data-type="questions">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg">
                        <path d="M2 14.75C2 16.5449 3.45507 18 5.25 18H5.99921L6 20.7499C6 21.0196 6.08736 21.2822 6.24901 21.4984C6.6625 22.0512 7.44585 22.1642 7.99868 21.7507L10.1239 20.1608L10.5199 18.5768C10.5914 18.2911 10.6965 18.0162 10.8322 17.7578L7.49986 20.2506L7.49879 16.5H5.25C4.2835 16.5 3.5 15.7165 3.5 14.75V6.25C3.5 5.2835 4.2835 4.5 5.25 4.5H18.75C19.7165 4.5 20.5 5.2835 20.5 6.25V10.0946C21.0475 10.2288 21.5671 10.5054 22 10.9246V6.25C22 4.45507 20.5449 3 18.75 3H5.25C3.45507 3 2 4.45507 2 6.25V14.75ZM18.0979 11.6695L12.1955 17.5719C11.8513 17.916 11.6072 18.3472 11.4892 18.8194L11.0315 20.6501C10.8325 21.4462 11.5536 22.1674 12.3497 21.9683L14.1804 21.5106C14.6526 21.3926 15.0838 21.1485 15.4279 20.8043L21.3303 14.9019C22.223 14.0093 22.223 12.5621 21.3303 11.6695C20.4377 10.7768 18.9905 10.7768 18.0979 11.6695Z"
                              fill="#242424"/>
                    </svg>
                    <?= Loc::getMessage('SA_COMMENT_TITLE') ?>
                </button>
            </div>
        </div>
    </div>
    <?php
    if (empty($tab) || $tab == 'reviews') {
        $APPLICATION->IncludeComponent(
            "sotbit:rvw.reviews",
            "",
            array(
                "DEFAULT_RATING_ACTIVE" => $arParams['DEFAULT_RATING_ACTIVE'],
                "MAX_RATING" => $arParams['MAX_RATING'],
                "TEXTBOX_MAXLENGTH" => $arParams['REVIEWS_TEXTBOX_MAXLENGTH'],
                "ID_ELEMENT" => $arParams['ID_ELEMENT'],
                "NOTICE_EMAIL" => $arParams['NOTICE_EMAIL'],
                "DATE_FORMAT" => $arParams['DATE_FORMAT'],
                "USER_AGREEMENT_ID" => $arParams['USER_AGREEMENT_ID'],
                "AJAX_MODE" => "Y",
            ),
            false
        );
    } else {
        $APPLICATION->IncludeComponent(
            "sotbit:rvw.questions",
            "",
            array(
                'ID_ELEMENT' => $arParams['ID_ELEMENT'],
                'TEXTBOX_MAXLENGTH' => $arParams['QUESTIONS_TEXTBOX_MAXLENGTH'],
                "NOTICE_EMAIL" => $arParams['NOTICE_EMAIL'],
                "DATE_FORMAT" => $arParams['DATE_FORMAT'],
                "USER_AGREEMENT_ID" => $arParams['USER_AGREEMENT_ID'],
            ),
            false
        );
    }

    } elseif ($arParams["SHOW_REVIEWS"] == 'Y' && $enableReview) {
        $APPLICATION->IncludeComponent(
            "sotbit:rvw.reviews",
            "",
            array(
                'DEFAULT_RATING_ACTIVE' => $arParams['DEFAULT_RATING_ACTIVE'],
                'MAX_RATING' => $arParams['MAX_RATING'],
                'TEXTBOX_MAXLENGTH' => $arParams['REVIEWS_TEXTBOX_MAXLENGTH'],
                'ID_ELEMENT' => $arParams['ID_ELEMENT'],
                "NOTICE_EMAIL" => $arParams['NOTICE_EMAIL'],
                "DATE_FORMAT" => $arParams['DATE_FORMAT'],
                "USER_AGREEMENT_ID" => $arParams['USER_AGREEMENT_ID'],
            ),
            false
        );
    } elseif ($arParams["SHOW_QUESTIONS"] == 'Y' && $enableQuestion) {
        $APPLICATION->IncludeComponent(
            "sotbit:rvw.questions",
            "",
            array(
                'ID_ELEMENT' => $arParams['ID_ELEMENT'],
                'TEXTBOX_MAXLENGTH' => $arParams['QUESTIONS_TEXTBOX_MAXLENGTH'],
                "NOTICE_EMAIL" => $arParams['NOTICE_EMAIL'],
                "DATE_FORMAT" => $arParams['DATE_FORMAT'],
                "USER_AGREEMENT_ID" => $arParams['USER_AGREEMENT_ID'],
            ),
            false
        );
    }
    ?>
</div>

<?
if ($arParams["SHOW_REVIEWS"] == 'Y') {
    $APPLICATION->IncludeComponent(
        "sotbit:rvw.reviews.add",
        "",
        array(
            'DEFAULT_RATING_ACTIVE' => $arParams['DEFAULT_RATING_ACTIVE'],
            'TEXTBOX_MAXLENGTH' => $arParams['REVIEWS_TEXTBOX_MAXLENGTH'],
            'MAX_RATING' => $arParams['MAX_RATING'],
            'ID_ELEMENT' => $arParams['ID_ELEMENT'],
            "NOTICE_EMAIL" => $arParams['NOTICE_EMAIL'],
        ),
        false
    );
}
?>
<script>
    function eventHandlerTab() {
        const buttonItemsTabs = document.querySelectorAll('.tab-reviews');

        for (let buttonItem of buttonItemsTabs) {

            buttonItem.addEventListener('click', (e) => {
                const tabsNode = document.querySelector('.reviews-tab-wrap');
                let btnActive = tabsNode.querySelector('.btn-reviews--active__tab');
                const btn = e.target.closest('.btn-reviews');

                for (let buttonItem of buttonItemsTabs) {
                    if (btnActive != btn) {
                        btnActive.classList.remove('btn-reviews--active__tab');
                        btn.classList.add('btn-reviews--active__tab');
                        btnActive = btn;
                    }
                }

                if (btn.dataset.type) {

                    var request = BX.ajax.runComponentAction('sotbit:rvw.base', 'contentLoader', {
                        signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>',
                        mode: 'class',
                        data: {
                            active: btn.dataset.type,
                        }
                    });

                    request.then(
                        function (response) {
                            let processed = BX.processHTML(response.data.html, false);
                            const template = document.createElement('template');
                            template.innerHTML = response.data.html;
                            document.querySelector('#reviews').replaceWith(template.content);
                            BX.ajax.processScripts(processed.SCRIPT);
                            SA_ReviewsAdd.initModals();
                            refreshFsLightbox();
                            const url = new URL(window.location);
                            url.searchParams.set('tab', btn.dataset.type);
                            history.pushState(null, null, url);
                            BX.onCustomEvent('sotbit:reviews.tabChange', [btn.dataset.type]);
                        }.bind(this),
                        function (response) {
                            console.log(response);
                        }.bind(this),
                    );
                }
            });
        }
    }
    BX.ready(function () {
        eventHandlerTab();
    });
</script>
