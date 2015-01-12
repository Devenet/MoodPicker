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

namespace Utils;

use Core\Config;

class Cookie {
    
    const SESSION = 0;
    const MINUTE = 60;
    const HOUR = 3600;
    const DAY = 86400;
    const MONTH = 2678400;

    static public function Name($name) {
        return sha1($_SERVER['SCRIPT_FILENAME'].Config::Get('app').'_'.$name);
    }
    
    static public function Add($name, $value = 1, $expire = Cookie::SESSION) {
        // name, value, expire, path, domain, secure, httponly
        setcookie(self::Name($name), $value, $expire == Cookie::SESSION ? 0 : time()+$expire,
            '/', $_SERVER['HTTP_HOST'], FALSE, TRUE);
    }
    
    static public function Exists($name) {
        return isset($_COOKIE[self::Name($name)]);
    }
    
    static public function Get($name) {
        if (! self::Exists($name)) { return NULL; }
        return htmlspecialchars($_COOKIE[self::Name($name)]);
    }
    
    static public function Remove($name) {
        setcookie (self::Name($name), '', time()-3600, '/', $_SERVER['HTTP_HOST']);
    }

}

?>