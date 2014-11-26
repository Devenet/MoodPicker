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
    
    const DB_EXTENSION = '.db';
    const INIT_EXTENSION = '.sql';

    private static $instances;
    private static $access = 0;

    static public function Instance($database = NULL) {
        if (! isset(self::$instances[$database])) {

            if (is_null($database)) { $db_info = Config::Get('database'); } 
            else { $db_info = array( 'name' => $database ); }
            
            $db_file = Config::Path(Config::DIR_DATA.DIRECTORY_SEPARATOR.$db_info['name'].SQLite::DB_EXTENSION);
            
            if (! file_exists($db_file)) {
                $schema = file_get_contents(Config::Path(Config::DIR_DATA.DIRECTORY_SEPARATOR.$db_info['name'].SQLite::INIT_EXTENSION));
                $schema = str_replace("\n", ' ', $schema);
                $schema = str_replace("\r", ' ', $schema);
                
                $db = new SQLite3($db_file, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
                $db->exec($schema);
            }

            // load database
            self::$instances[$database] = new PDO('sqlite:'.$db_file);
            if (Config::Get('debug')) { self::$instances[$database]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); }
            else { self::$instances[$database]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT); }
            self::$instances[$database]->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        }
        self::$access++;
        return self::$instances[$database];
    }
    
    static public function Access() {
        return self::$access;
    }

}

?>