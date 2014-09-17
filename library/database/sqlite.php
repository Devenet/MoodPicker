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

namespace Database;

use \PDO;
use \SQLite3;
use Core\Config;

class SQLite {
    
    private static $instance;
    private static $access = 0;

    static public function Instance() {
        if (! isset(self::$instance)) {
            $db_info = Config::Get('database');
            $db_file = Config::Path(Config::DIR_DATA.DIRECTORY_SEPARATOR.$db_info['name']);
            
            if (file_exists($db_file)) {
                self::$instance = new PDO('sqlite:'.$db_file);
            
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }
            else {
                $schema = file_get_contents(Config::Path(Config::DIR_DATA.DIRECTORY_SEPARATOR.$db_info['init']));
                $schema = str_replace("\n", ' ', $schema);
                $schema = str_replace("\r", ' ', $schema);
                
                $db = new SQLite3($db_file, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
                $db->exec($schema);
                
                echo 'Database file has just been created. Reload the webapp.';
                exit();
            }
        }
        self::$access++;
        return self::$instance;
    }
    
    static public function Access() {
        return self::$access;
    }

}

?>