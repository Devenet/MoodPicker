<?php

/*
Copyright 2014 - Nicolas Devenet <nicolas@devenet.info>

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Code source hosted on https://github.com/nicolabricot/MoodPicker
*/

use Picker\Mood;
use Picker\MoodLevel;
use Utils\TextHelper;
use Utils\Cookie;

$this->fakePage('details');

// year prelude
$years = Mood::YearsAvailable();
if (!in_array(date('Y'), $years)) { $years[] = date('Y'); rsort($years); }
$year = $this->request(1);

if (is_null($year)) { $year = date('Y'); }
else if (! checkdate(1, 1, $year) || ! in_array($year, $years)) {
    header('Location: '.$this->URL('details'));
    exit();
}

$year += 0;
$this->assign('years', $years);
$this->assign('year', $year);

// month prelud
$months = Mood::MonthsAvailable($year);
$month = $this->request(2);

// shared data prelude
$s = "
$(function() {
    Chart.defaults.global.tooltipFontFamily = $('body').css('font-family');
    Chart.defaults.global.tooltipFontSize = 12;
";



// month details page
if (! is_null($month)) {

    if (! checkdate($month, 1, $year) || ! in_array($month, $months)) {
        header('Location: '.$this->URL('details/'.$year));
        exit();
    }

    $month += 0;
    $this->page('details/month');

    $this->assign('months', $months);
    $this->assign('month', $month);
    $this->assign('month_txt', date('F', mktime(0, 0, 0, $month, 1)));

    // current month stats
    $month_moods = Mood::CountMonthMoods($month, $year);
    $month_nb_moods = $month_moods['count'];
    $month_nb_goods = $month_moods[MoodLevel::GOOD];
    $month_nb_bads = $month_moods[MoodLevel::BAD];
    if (empty($month_nb_moods)) { $month_nb_moods = 1; }
    $this->assign('month_goods_percentage', $month_nb_goods * 100 / $month_nb_moods);
    $this->assign('month_bads_percentage', $month_nb_bads * 100 / $month_nb_moods);
    $this->assign('month_goods', $month_nb_goods);
    $this->assign('month_bads', $month_nb_bads);


}
// year details page
else {

    $this->page('details/year');

    // current year stats
    $year_moods = Mood::CountYearMoods($year);
    $year_nb_moods = $year_moods['count'];
    $year_nb_goods = $year_moods[MoodLevel::GOOD];
    $year_nb_bads = $year_moods[MoodLevel::BAD];
    if (empty($year_nb_moods)) { $year_nb_moods = 1; }
    $this->assign('year_goods_percentage', $year_nb_goods * 100 / $year_nb_moods);
    $this->assign('year_bads_percentage', $year_nb_bads * 100 / $year_nb_moods);
    $this->assign('year_goods', $year_nb_goods);
    $this->assign('year_bads', $year_nb_bads);

    // months stats
    $diplayMonths = array();
    $monthsGraph = array();
    foreach ($months as $m) {
        $monthMoods = Mood::CountMonthMoods($m, $year);
        $t = $monthMoods['count'];
        $tg = $monthMoods[MoodLevel::GOOD];
        $tb = $monthMoods[MoodLevel::BAD];
        $diplayMonths[$m] = array(
            'total' => $t,
            'goods' => $tg,
            'bads' => $tb,
            'goods_percentage' => $tg * 100 / $t,
            'bads_percentage' => $tb * 100 / $t
        );
        $monthsGraph['name'][] = date('M.', mktime(0, 0, 0, $m, 1));
        $monthsGraph['value'][] = round($diplayMonths[$m]['goods_percentage'], 2);
        $s .= "
            new Chart(document.getElementById('chartMonth".$m."').getContext('2d')).Doughnut(
                [
                    {
                        value: ".$tb.",
                        color: $('#color_picker .progress-bar-danger').css('background-color'),
                        label: 'Bad Mood'
                    },
                    {
                        value: ".$tg.",
                        color: $('#color_picker .progress-bar-success').css('background-color'),
                        label: 'Good Mood'
                    }
                ]
                , { 
                    animation: true,
                    animationEasing: 'linear', animationSteps: 25,
                    segmentShowStroke: false,
                    percentageInnerCutout : 60
                }
            );
        ";
    }
    $this->assign('months', $diplayMonths);

    // draw year line graph
    if (count($months) > 1) {
        $monthsGraph = array_map('array_reverse', $monthsGraph);
        $s .= "
        var dataYear = {
            labels: ['".implode("', '", $monthsGraph['name'])."'],
            datasets: [
                {
                    label: 'Mood',
                    fillColor: 'rgba(220,220,220,0.2)',
                    strokeColor: $('#color_picker .progress-bar-success').css('background-color'),
                    pointColor: $('#color_picker .progress-bar-success').css('background-color'),
                    data: [".implode(", ", $monthsGraph['value'])."]
                }
            ]
        };
        var chartYear = new Chart(document.getElementById('chartYear').getContext('2d')).Line(dataYear, { responsive: true });
        ";
    } else {
        $s .= "$('#chartYear').parent().hide();";
    }

    // wait loading
    $this->register('script', TextHelper::RemoveLineBreak("
    $(function() {
        $('a.details-year').on('click', function(){
            $('.modal').modal({show: true});
            var modal = $('#loadingModal');
            setTimeout(function() {
                modal.html('Still loading&hellip;');
                setTimeout(function() {
                    modal.html('Still loading all the amazing data&hellip;');
                }, 2000);
            }, 1000);
        });
    });"));

}

   
// shared data postlude
$s .= '});';
$this->register('script', TextHelper::RemoveLineBreak($s));
$this->register('script_file', 'chart.min.js');

?>