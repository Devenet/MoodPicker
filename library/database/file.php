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

namespace Database;

use Core\Config;

class File {

    const FLAG_APPEND = FILE_APPEND;
    const FLAG_LOCK = LOCK_EX;

    protected $file;
    
    public function __construct($filename) {
        $this->file = Config::DIR_DATA.DIRECTORY_SEPARATOR.$filename;
    }

    public function exists() {
        return is_file($this->file);
    }
    public function getFile() {
        return $this->file;
    }

    public function save($data, $flags = 0) {
        return file_put_contents($this->file, $data, $flags);
    }
    public function get() {
        return $this->exists() ? file_get_contents($this->file) : NULL;
    }

    public function delete() {
        return unlink($this->file);
    }

}


?>