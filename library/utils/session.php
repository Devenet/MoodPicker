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

namespace Utils;

use Core\Config;
use Core\Setting;

class Session {

    static public function Add($name, $value = 1) {
        $_SESSION[Setting::APP_NAME.'_'.$name] = $value;
    }

    static public function Exists($name) {
        return isset($_SESSION[Setting::APP_NAME.'_'.$name]);
    }

    static public function Get($name) {
        if (! self::Exists($name)) { return NULL; }
        return htmlspecialchars($_SESSION[Setting::APP_NAME.'_'.$name]);
    }

    static public function Remove($name) {
        $_SESSION[Setting::APP_NAME.'_'.$name] = NULL;
        unset($_SESSION[Setting::APP_NAME.'_'.$name]);
    }

}

?>
