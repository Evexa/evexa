<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");


use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Sotbit\Reviews\Analytic\Helper;

if (!Loader::includeModule('iblock') || !Loader::includeModule('sotbit.reviews')) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $requestSites
 * @var string $requestDateOne
 * @var string $requestDateTwo
 */

if ($APPLICATION->GetGroupRight(\CSotbitReviews::iModuleID) === "D") {
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

if (!(new \CSotbitReviews())->getDemo()) {
    die();
}

\CJSCore::Init(['sotbit.analytic']);

IncludeModuleLangFile(__FILE__);

$tableId = "tbl_sotbit_reviews_analytic";
$oSort = new CAdminSorting($tableId);
$lAdmin = new CAdminList($tableId, $oSort);

if ($lAdmin->IsDefaultFilter()) {
    $find_cnt = "Y";
    $find_likes = "Y";
    $find_dislikes = "Y";
}

$lAdmin->InitFilter(["requestDateOne", "requestDateTwo", "find_site_id"]);

$arSite = Helper::getSites();
$sites = $requestSites ?: $arSite['reference_id'];

$dateFrom = $requestDateOne ? new DateTime($requestDateOne) : (new DateTime());
$dateTo = ($requestDateTwo ? new DateTime($requestDateTwo) : new DateTime())->add("+23 hours")->add("+59 minutes");
$dateFromFormat = $dateFrom->format('Y-m-d');
$dateToFormat = $dateTo->format('Y-m-d');
$metricsCurrentPeriod = new \Sotbit\Reviews\Analytic\Metrics($sites, $dateFrom, $dateTo);

$difference = intval(abs(strtotime($dateFromFormat) - strtotime($dateToFormat))) + (3600 * 24);
$previousDateFrom = new DateTime(date("d.m.Y", strtotime($dateFrom) - $difference));
$previousDateTo = (new DateTime(date("d.m.Y", strtotime($dateTo) - $difference)))->add("+23 hours")->add("+59 minutes");
$previousDateFromFormat = $previousDateFrom->format('Y-m-d');
$previousDateToFormat = $previousDateTo->format('Y-m-d');
$metricsPreviousPeriod = new \Sotbit\Reviews\Analytic\Metrics($sites, $previousDateFrom, $previousDateTo);

$lAdmin->BeginPrologContent();
if (!$requestDateOne && !$requestDateTwo) {
    CAdminMessage::ShowMessage(Loc::getMessage('PERIOD_NOT_SELECTED'));
} else {
?>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {'packages': ['corechart']});

        var chartFormatter = null;

        function formatChartData(data, columnIndex) {
            if (typeof google === 'undefined' || typeof google.visualization === 'undefined') {
                setTimeout(function() { formatChartData(data, columnIndex); }, 100);
                return;
            }

            if (!chartFormatter && typeof google.visualization.NumberFormat !== 'undefined') {
                try {
                    chartFormatter = new google.visualization.NumberFormat({pattern: '#,##0.00'});
                } catch(e) {
                    console.warn('Formatter creation error:', e);
                    return;
                }
            }

            if (chartFormatter) {
                try {
                    chartFormatter.format(data, columnIndex);
                } catch(e) {
                    console.warn('Format error:', e);
                }
            }
        }
    </script>
<?php //TODO Reviews timeline ?>
    <div class="title-graph p-top-graph-20">
        <?= Loc::getMessage("REVIEWS_PERIOD", ['#DATA_FROM#' => $dateFrom, "#DATE_TO#" => $dateTo]) ?>
    </div>
    <script type="text/javascript">
        google.charts.setOnLoadCallback(drawChart_REVIEWS_TIMELINE);

        function drawChart_REVIEWS_TIMELINE() {
            var data = google.visualization.arrayToDataTable([
                [
                    '<?=Loc::getMessage("DATES") ?>',
                    '<?=Loc::getMessage("CNT_REVIEWS") ?>',
                    '<?=Loc::getMessage("CNT_LIKES") ?>',
                    '<?=Loc::getMessage("CNT_DISLIKES") ?>'
                ],
                <?
                foreach ($metricsCurrentPeriod->arDateFormat as $date) {
                    $ctnReviews = $metricsCurrentPeriod->counterByTime[$date]['CNT_REVIEWS'] ?: 0;
                    $ctnLike = $metricsCurrentPeriod->counterByTime[$date]['CNT_LIKES'] ?: 0;
                    $ctnDislike = $metricsCurrentPeriod->counterByTime[$date]['CNT_DISLIKES'] ?: 0;

                    echo
                    "['$date'
                    , {$ctnReviews}
                    , {$ctnLike}
                    , {$ctnDislike}],";
                }
                ?>
            ]);

            var options = {
                title: '',
                hAxis: {
                    title: '<?=Loc::getMessage("DATES") ?>',
                    titleTextStyle: {color: '#333'}
                },
                vAxis: {minValue: 0}
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_div_reviews'));

            console.log([
                [
                    '<?=Loc::getMessage("DATES") ?>',
                    '<?=Loc::getMessage("CNT_REVIEWS") ?>',
                    '<?=Loc::getMessage("CNT_LIKES") ?>',
                    '<?=Loc::getMessage("CNT_DISLIKES") ?>'
                ],
                <?
                foreach ($metricsCurrentPeriod->arDateFormat as $date) {
                    $ctnReviews = $metricsCurrentPeriod->counterByTime[$date]['CNT_REVIEWS'] ?: 0;
                    $ctnLike = $metricsCurrentPeriod->counterByTime[$date]['CNT_LIKES'] ?: 0;
                    $ctnDislike = $metricsCurrentPeriod->counterByTime[$date]['CNT_DISLIKES'] ?: 0;

                    echo
                    "['$date'
                    , {$ctnReviews}
                    , {$ctnLike}
                    , {$ctnDislike}],";
                }
                ?>
            ], options)
            chart.draw(data, options);
        }
    </script>
    <div id="chart_div_reviews" class="body-graph"></div>
<?php
//TODO Reviews CNT_REVIEWS
if ($metricsCurrentPeriod->counter['CNT_REVIEWS'] > 0) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("COMPARE_REVIEWS_CNT") ?></div>
    <div id="piechart_cnt_reviews" class="body-graph"></div>
    <script type="text/javascript">

        google.charts.setOnLoadCallback(drawChart_COMPARE_REVIEWS_CNT);

        function drawChart_COMPARE_REVIEWS_CNT() {
            var data = google.visualization.arrayToDataTable([
                ['Period', 'Cnts'],
                ['<?=Loc::getMessage("CUR_PERIOD")?> <?=$dateFromFormat?> - <?=$dateToFormat?>', <?=$metricsCurrentPeriod->counter['CNT_REVIEWS']?>],
                ['<?=Loc::getMessage("PREV_PERIOD")?> <?=$previousDateFromFormat?> - <?=$previousDateToFormat?>', <?=$metricsPreviousPeriod->counter['CNT_REVIEWS']?>],
            ]);
            var options = {title: ''};
            var chart = new google.visualization.PieChart(document.getElementById('piechart_cnt_reviews'));
            chart.draw(data, options);
        }
    </script>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("CNT_REVIEWS_NO") ?></div>
<?php } ?>

<?php
//TODO Reviews CNT_LIKES
if ($metricsCurrentPeriod->counter['CNT_LIKES'] > 0 || $metricsPreviousPeriod->counter['CNT_LIKES']) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("COMPARE_LIKES_CNT") ?></div>
    <script type="text/javascript">

        google.charts.setOnLoadCallback(drawChart_COMPARE_LIKES_CNT);

        function drawChart_COMPARE_LIKES_CNT() {
            var data = google.visualization.arrayToDataTable([
                ['Period', 'Cnts'],
                ['<?=Loc::getMessage("CUR_PERIOD")?> <?=$dateFromFormat?> - <?=$dateToFormat?>', <?=$metricsCurrentPeriod->counter['CNT_LIKES']?>],
                ['<?=Loc::getMessage("PREV_PERIOD")?> <?=$previousDateFromFormat?> - <?=$previousDateToFormat?>', <?=$metricsPreviousPeriod->counter['CNT_LIKES']?>],
            ]);
            var options = {title: ''};
            var chart = new google.visualization.PieChart(document.getElementById('piechart_cnt_likes'));
            chart.draw(data, options);
        }
    </script>
    <div id="piechart_cnt_likes" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty">
        <?= Loc::getMessage("CNT_LIKES_NO") ?>
    </div>
<?php } ?>

<?php
//TODO Reviews CNT_DISLIKES
if ($metricsCurrentPeriod->counter['CNT_DISLIKES'] > 0 || $metricsPreviousPeriod->counter['CNT_DISLIKES'] > 0) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("COMPARE_DISLIKES_CNT") ?></div>
    <script type="text/javascript">
        google.charts.setOnLoadCallback(drawChart_COMPARE_DISLIKES_CNT);

        function drawChart_COMPARE_DISLIKES_CNT() {
            var data = google.visualization.arrayToDataTable([
                ['Period', 'Cnts'],
                ['<?=Loc::getMessage("CUR_PERIOD")?> <?=$dateFromFormat?> - <?=$dateToFormat?>', <?=$metricsCurrentPeriod->counter['CNT_DISLIKES']?>],
                ['<?=Loc::getMessage("PREV_PERIOD")?> <?=$previousDateFromFormat?> - <?=$previousDateToFormat?>', <?=$metricsPreviousPeriod->counter['CNT_DISLIKES']?>],
            ]);
            var options = {title: ''};
            var chart = new google.visualization.PieChart(document.getElementById('piechart_cnt_dislikes'));
            chart.draw(data, options);
        }
    </script>
    <div id="piechart_cnt_dislikes" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("CNT_DISLIKES_NO") ?></div>
<?php } ?>

<?php
//TODO Reviews CNT_USERS
if (!empty($metricsCurrentPeriod->counterByUser)) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("CNT_USERS") ?></div>
    <script type="text/javascript">
        google.charts.setOnLoadCallback(drawChart_CNT_USERS);

        function drawChart_CNT_USERS() {
            var data = google.visualization.arrayToDataTable([
                ["User", "Adds", {role: "style"}],
                <?php
                foreach ($metricsCurrentPeriod->counterByUser as $id => $value) {
                    echo '["' . $metricsCurrentPeriod->dataUserName[$id] . '", ' . $value['CNT_REVIEWS'] . ', "#00F"],';
                }
                ?>
            ]);

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
                {
                    calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation"
                },
                2]);

            var options = {
                title: "",
                width: 900,
                height: 500,
                bar: {groupWidth: "95%"},
                legend: {position: "none"},
            };

            var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values"));
            chart.draw(view, options);
            chart.draw(data, options);
        }
    </script>
    <div id="columnchart_values" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("CNT_USERS_NO") ?></div>
<?php } ?>


<?php
//TODO Reviews CNT_MODERATED
if (!empty($metricsCurrentPeriod->counterByReviewModerate)) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("CNT_MODERATED") ?></div>
    <script type="text/javascript">
        google.charts.setOnLoadCallback(drawChart_CNT_MODERATED);

        function drawChart_CNT_MODERATED() {
            var data = google.visualization.arrayToDataTable([
                ['Moderation', 'Cnts'],
                ['<?=Loc::getMessage("MODERATED_Y")?>', <?=($metricsCurrentPeriod->counterByReviewModerate['Y'] ?: 0)?>],
                ['<?=Loc::getMessage("MODERATED_N")?>', <?=($metricsCurrentPeriod->counterByReviewModerate['N'] ?: 0)?>],
            ]);

            var chart = new google.visualization.PieChart(document.getElementById('piechart_cnt_moderated'));
            chart.draw(data, {title: ''});
        }
    </script>
    <div id="piechart_cnt_moderated" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("CNT_MODERATED_NO") ?></div>
<?php } ?>

<?php
//TODO Reviews MONEY_USERS
if (!empty($metricsCurrentPeriod->counterByUser)) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("MONEY_USER") ?></div>
    <script type="text/javascript">
        google.charts.setOnLoadCallback(drawChart_MONEY_USERS);

        function drawChart_MONEY_USERS() {
            var data = google.visualization.arrayToDataTable([
                ["User", "Money", {role: "style"}],
                <?php
                foreach ($metricsCurrentPeriod->counterByUser as $id => $value) {
                    echo '["' . $metricsCurrentPeriod->dataUserName[$id] . '", ' . $value['MONEY'] . ', "#00F"],';
                }
                ?>
            ]);
            formatChartData(data, 1);

            var view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
                {
                    calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation"
                },
                2]);

            var options = {
                title: "",
                width: 900,
                height: 500,
                bar: {groupWidth: "95%"},
                legend: {position: "none"},
                vAxis: { format: "#,##0.00" },
            };
            var chart = new google.visualization.ColumnChart(document.getElementById("columnchart_values_money_users"));
            chart.draw(view, options);
            chart.draw(data, options);
        }
    </script>
    <div id="columnchart_values_money_users" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("MONEY_USERS_NO") ?></div>
<?php } ?>

<?php
//TODO Reviews MONEY_NEW_REVIEW
if ($metricsCurrentPeriod->counter['MONEY_REVIEWS'] > 0 || $metricsPreviousPeriod->counter['MONEY_REVIEWS'] > 0) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("MONEY_NEW_REVIEWS") ?></div>
    <script type="text/javascript">
        google.charts.setOnLoadCallback(drawChart_COMPARE_MONEY_REVIEWS);

        function drawChart_COMPARE_MONEY_REVIEWS() {

            var data = google.visualization.arrayToDataTable([
                ['Period', 'Cnts'],
                ['<?=Loc::getMessage("CUR_PERIOD")?> <?=$dateFromFormat?> - <?=$dateToFormat?>', <?= $metricsCurrentPeriod->counter['MONEY_REVIEWS']?>],
                ['<?=Loc::getMessage("PREV_PERIOD")?> <?=$previousDateFromFormat?> - <?=$previousDateToFormat?>', <?=$metricsPreviousPeriod->counter['MONEY_REVIEWS']?>],
            ]);

            var options = {title: ''};
            var chart = new google.visualization.PieChart(document.getElementById('piechart_MONEY_REVIEWS'));

            formatChartData(data, 1);
            chart.draw(data, options);
        }
    </script>
    <div id="piechart_MONEY_REVIEWS" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("MONEY_REVIEWS_NO") ?></div>
<?php } ?>

<?php
//TODO Reviews MONEY_LIKES
if ($metricsCurrentPeriod->counter['MONEY_LIKES'] > 0 || $metricsPreviousPeriod->counter['MONEY_LIKES'] > 0) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("MONEY_LIKES") ?></div>
    <script type="text/javascript">
        google.charts.setOnLoadCallback(drawChart_COMPARE_MONEY_LIKES);

        function drawChart_COMPARE_MONEY_LIKES() {
            var data = google.visualization.arrayToDataTable([
                ['Period', 'Cnts'],
                [
                    '<?=Loc::getMessage("CUR_PERIOD")?> <?=$dateFromFormat?> - <?=$dateToFormat?>',
                    <?=$metricsCurrentPeriod->counter['MONEY_LIKES']?>
                ],
                [
                    '<?=Loc::getMessage("PREV_PERIOD")?> <?=$previousDateFromFormat?> - <?=$previousDateToFormat?>',
                    <?=$metricsPreviousPeriod->counter['MONEY_LIKES']?>
                ],
            ]);

            var options = {title: ''};
            var chart = new google.visualization.PieChart(document.getElementById('piechart_MONEY_LIKES'));

            formatChartData(data, 1);
            chart.draw(data, options);
        }
    </script>
    <div id="piechart_MONEY_LIKES" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("MONEY_LIKES_NO") ?></div>
<?php } ?>

<?php
//TODO Reviews MONEY_DISLIKES
if ($metricsCurrentPeriod->counter['MONEY_DISLIKES'] > 0 || $metricsPreviousPeriod->counter['MONEY_DISLIKES'] > 0) {
    ?>
    <div class="title-graph p-top-graph-20"><?= Loc::getMessage("MONEY_DISLIKES") ?></div>
    <script type="text/javascript">

        google.charts.setOnLoadCallback(drawChart_COMPARE_MONEY_DISLIKES);

        function drawChart_COMPARE_MONEY_DISLIKES() {

            var data = google.visualization.arrayToDataTable([
                ['Period', 'Cnts'],
                [
                    '<?=Loc::getMessage("CUR_PERIOD")?> <?=$dateFromFormat?> - <?=$dateToFormat?>'
                    , <?=$metricsCurrentPeriod->counter['MONEY_DISLIKES']?>
                ],
                [
                    '<?=Loc::getMessage("PREV_PERIOD")?> <?=$previousDateFromFormat?> - <?=$previousDateToFormat?>'
                    , <?=$metricsPreviousPeriod->counter['MONEY_DISLIKES']?>
                ],
            ]);

            var options = {title: ''};
            var chart = new google.visualization.PieChart(document.getElementById('piechart_MONEY_DISLIKES'));

            formatChartData(data, 1);
            chart.draw(data, options);
        }
    </script>
    <div id="piechart_MONEY_DISLIKES" class="body-graph"></div>
<?php } else { ?>
    <div class="title-graph title-graph__empty"><?= Loc::getMessage("MONEY_LIKES_NO") ?></div>
<?php } ?>
<?php } ?>
<?php
$lAdmin->EndPrologContent();
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("STAT_PAGE_TITLE"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter($tableId . "_filter", [Loc::getMessage("STAT_SITE")]);
?>
    <form name="find_form" method="GET" action="<?= $APPLICATION->GetCurPage() ?>?">
        <? $oFilter->Begin(); ?>
        <tr>
            <td><?= Loc::getMessage("STAT_PERIOD") . " (" . FORMAT_DATE . "):" ?></td>
            <td><?= CalendarPeriod("requestDateOne", $requestDateOne, "requestDateTwo", $requestDateTwo, "find_form", "Y") ?></td>
        </tr>
        <tr>
            <td><?= Loc::getMessage("STAT_SITE") ?>:</td>
            <td><?= SelectBoxMFromArray("requestSites[]", $arSite, $requestSites, "", ""); ?></td>
        </tr>
        <?php
        $oFilter->Buttons([
            "table_id" => $tableId,
            "url" => $APPLICATION->GetCurPage(),
            "form" => "find_form"
        ]);
        $oFilter->End();
        ?>
    </form>
<?php
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>

