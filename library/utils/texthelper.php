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

abstract class TextHelper {
    
    static public function removeLineBreak($string) {
        $string = preg_replace('/^\s+|\r|\s+$/m', '', $string);
        return preg_replace('/^\s+|\n|\s+$/m', ' ', $string);
    }

    static public function niceVersion($version) {
    	return preg_replace('#(\d+\.\d+)(\.\d+)#', '$1', $version);
    }
}


?>