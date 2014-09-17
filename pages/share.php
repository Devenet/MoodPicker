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

use Utils\Cookie;
use Picker\Mood;
use Picker\MoodLevel;

if (isset($_POST['mood']) && $this->acceptToken()) {
    $_POST['mood'] = $_POST['mood']+0;
    
    if (! MoodLevel::isValidValue($_POST['mood']))
        $this->errorPage('Invalid value', 'The given value for your current mood is unknow.');
    
    
    if (! Cookie::Exists('voted')) {
        $m = new Mood($_POST['mood'] , time(), $_SERVER['REMOTE_ADDR']);
        $m->save();
        Cookie::add('voted', true, Cookie::HOUR*2);
        
        header('Location: ./');
        exit();
    }
    
    $this->errorPage('Already voted', 'An entry has already been enregistred from your computer. You have to wait some times before submitting an other mood.');
    
}
else {
    $this->getToken();
    $this->assign('good', MoodLevel::GOOD);
    $this->assign('bad', MoodLevel::BAD);
}

?>