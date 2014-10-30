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

$moods = Mood::DayMoods();
$bads = array();
$goods = array();
foreach ($moods as $m) {
    if ($m->getMood() == MoodLevel::GOOD)
        $goods[] = $m;
    else
        $bads[] = $m;
}

// results of the day
$this->assign('moods', $moods);
if (count($moods) > 0) {
    $this->assign('goods_percentage', count($goods) * 100 / count($moods));
    $this->assign('bads_percentage', count($bads) * 100 / count($moods));
    $s = "
    $(function(){
        var data = [
            {
                value: ".count($bads).",
                color: $('#color_picker .progress-bar-danger').css('background-color'),
                label: 'Bad Mood'
            },
            {
                value: ".count($goods).",
                color: $('#color_picker .progress-bar-success').css('background-color'),
                label: 'Good Mood'
            }
        ]; 
    ";
    if (count($moods) > 0) {
        $s .= "
            new Chart(document.getElementById('dayChart').getContext('2d')).Doughnut(data, { 
                animation: true, animationEasing: 'linear', animationSteps: 25,
                tooltipFontFamily: $('body').css('font-family'),
                tooltipFontSize: 12,
                segmentShowStroke: false,
                percentageInnerCutout : 60
            });
        ";
    }
    $s .= "});";
    $this->register('script', TextHelper::removeLineBreak($s));
}

// stats of the month
$month = Mood::CountMonthMoods();
$month_goods = Mood::CountMonthMoods(NULL, NULL, MoodLevel::GOOD);
$month_bads = $month - $month_goods;
if (empty($month)) { $month = 1; }
$this->assign('month_goods_percentage', $month_goods * 100 / $month);
$this->assign('month_bads_percentage', $month_bads * 100 / $month);
$this->assign('month_goods', $month_goods);
$this->assign('month_bads', $month_bads);

// javascript dependancy
$this->register('script_file', 'chart.min.js');

?>