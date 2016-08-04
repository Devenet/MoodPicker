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
use DataBase\File;

class SerializedFile extends File {

    const PREFIX = '<?php /* ';
    const SUFFIX = ' */ ?>';
    const EXTENSION = '.php';

    public function __construct($filename) {
        parent::__construct($filename.SerializedFile::EXTENSION);
    }

    public function save($data, $flags = 0) {
        $data = SerializedFile::PREFIX.base64_encode(gzdeflate(serialize($data))).SerializedFile::SUFFIX;
        return parent::save($data, $flags);
    }

    public function get() {
        $data = parent::get();
        if (empty($data)) { return $data; }
        return unserialize(gzinflate(base64_decode(substr($data, strlen(SerializedFile::PREFIX), -strlen(SerializedFile::SUFFIX)))));
    }

}

?>