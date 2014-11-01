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
use Utils\Session;
use Utils\Cookie;
use Utils\TextHelper;

// results of the day
$dayMoods = Mood::CountDayMoods();
$dayBads = $dayMoods[MoodLevel::BAD];
$dayGoods = $dayMoods[MoodLevel::GOOD];
$dayCount = $dayMoods['count'];
$this->assign('moods', $dayCount);
if ($dayCount > 0) {
    $this->assign('goods_percentage', $dayGoods * 100 / $dayCount);
    $this->assign('bads_percentage', $dayBads * 100 / $dayCount);
    $s = "
    $(function(){
        var data = [
            {
                value: ".$dayBads.",
                color: $('#color_picker .progress-bar-danger').css('background-color'),
                label: 'Bad Mood'
            },
            {
                value: ".$dayGoods.",
                color: $('#color_picker .progress-bar-success').css('background-color'),
                label: 'Good Mood'
            }
        ]; 
        new Chart(document.getElementById('dayChart').getContext('2d')).Doughnut(data, { 
            animation: true, animationEasing: 'linear', animationSteps: 25,
            tooltipFontFamily: $('body').css('font-family'),
            tooltipFontSize: 12,
            segmentShowStroke: false,
            percentageInnerCutout : 60
        });
    });";
    $this->register('script', TextHelper::removeLineBreak($s));
}

// stats of the month
$monthMoods = Mood::CountMonthMoods();
$monthGoods = $monthMoods[MoodLevel::GOOD];
$monthBads = $monthMoods[MoodLevel::BAD];
$monthCount = max(1, $monthBads+$monthGoods);
$this->assign('month_goods_percentage', $monthGoods * 100 / $monthCount);
$this->assign('month_bads_percentage', $monthBads * 100 / $monthCount);
$this->assign('month_goods', $monthGoods);
$this->assign('month_bads', $monthBads);

// javascript dependancy
if ($dayCount > 0) { $this->register('script_file', 'chart.min.js'); }

?>