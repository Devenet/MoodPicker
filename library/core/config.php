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

namespace Core;

abstract class Config {
    
    static private $entries = array(
        'app', 'name', 'title',
        'description', 'copyright',
        'theme', 'themes',
        'database', 'api',
        'debug'
    );
    static private $defaultEntries = array(
        'app' => 'mood_picker',
        'name' => 'Mood Picker',
        'title' => 'Share your mood!',
        'copyright' => 'All rights reserved',
        'database' => array(
            'type' => 'sqlite',
            'name' => 'mood_picker'
        ),
        'themes' => array('default'),
        'debug' => false
    );
    static private $values = NULL;
    
    const DIR_PAGES = 'pages';
    const DIR_TEMPLATES = 'templates';
    const DIR_DATA = 'data';
    
    const FILE_CONFIG = 'config.php';

    static private function DefaultValue($entry) {
        return array_key_exists($entry, self::$defaultEntries) ? self::$defaultEntries[$entry] : NULL;
    }
    
    static public function Get($name) {
        if (is_null(self::$values)) {
            
            if (is_file(self::FILE_CONFIG))
                require_once self::Path(self::FILE_CONFIG);

            foreach(self::$entries as $entry) {
                self::$values[$entry] = (array_key_exists($entry, $_CONFIG)) ? $_CONFIG[$entry] : self::DefaultValue($entry);
            }
        }
        return self::$values[$name];
    }
    
    static public function Path($filename = '') {
        return dirname(__FILE__).'/../../'.$filename;
    }

    static public function IP() {
        $ip = $_SERVER["REMOTE_ADDR"];
        // Then we use more HTTP headers to prevent session hijacking from users behind the same proxy
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { $ip=$ip.'_'.$_SERVER['HTTP_X_FORWARDED_FOR']; }
        if (isset($_SERVER['HTTP_CLIENT_IP'])) { $ip=$ip.'_'.$_SERVER['HTTP_CLIENT_IP']; }
        return htmlspecialchars($ip);
    }

}

?>