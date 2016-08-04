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

Code source hosted on https://github.com/Devenet/MoodPicker
*/

use Utils\Cookie;
use Utils\Session;
use Picker\Mood;
use Picker\MoodLevel;
use Core\Config;

if (isset($_POST['mood']) && $this->acceptToken()) {
    $_POST['mood'] = $_POST['mood']+0;
    
    if (! MoodLevel::isValidValue($_POST['mood']))
        $this->errorPage('Invalid value', 'The given value for your current mood is unknow.');
    
    if (!Cookie::Exists('voted') && !Session::Exists('voted')) {
        $m = new Mood($_POST['mood'] , time(), Config::IP());
        $m->save();
        Cookie::add('voted', true, Cookie::HOUR*2);
        Session::add('voted', true);
        
        header('Location: ./review');
        exit();
    }
    
    $this->errorPage('Already voted', 'An entry has already been enregistred from your computer. <br />You have to wait some times before submitting an other mood.');
    
}
else {
    $this->getToken();
    $this->assign('good', MoodLevel::GOOD);
    $this->assign('bad', MoodLevel::BAD);
}

?>