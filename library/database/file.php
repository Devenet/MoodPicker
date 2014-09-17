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

use Core\Config;

class File {

    const PHPPREFIX = '<?php /* ';
    const PHPSUFFIX = ' */ ?>';
    const PHPEXTENSION = '.php';
    
    private static $instance;
    private static $access = 0;
    private static $filename;

    static public function Instance($filename) {
        self::$filename = $filename.self::PHPEXTENSION;

        if (! isset(self::$instance)) {
            if (! file_exists(Config::DIR_DATA.DIRECTORY_SEPARATOR.self::$filename)) {
                $data = array();
                file_put_contents(Config::DIR_DATA.DIRECTORY_SEPARATOR.self::$filename,
                    self::PHPPREFIX.base64_encode(gzdeflate(serialize($data))).self::PHPSUFFIX
                );
            }
            self::$instance = new FileData(self::$filename);
        }
        self::$access++;
        return self::$instance;
    }
    
    static public function Access() {
        return self::$access;
    }

}

class FileData {

    private $filename;
    private $data;

    public function __construct($filename) {
        $this->filename = $filename;
        $this->data = $this->read();
    }

    public function GetData() {
        return $this->data;
    }

    public function SaveData($data) {
        $this->data = $data;
        $this->write();
    }

    private function read() {
        return unserialize(gzinflate(base64_decode(substr(
            file_get_contents(Config::DIR_DATA.DIRECTORY_SEPARATOR.$this->filename),
            strlen(File::PHPPREFIX), -strlen(File::PHPSUFFIX))))
        );
    }
    private function write() {
        file_put_contents(Config::DIR_DATA.DIRECTORY_SEPARATOR.$this->filename,
            File::PHPPREFIX.base64_encode(gzdeflate(serialize($this->data))).File::PHPSUFFIX
        );
    }

}

?>