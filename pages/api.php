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

use Picker\API;
use Utils\Cookie;
use Utils\TextHelper;

$api = new API();

switch($this->request(1)) {
    case 'documentation':
        $this->page('api/documentation');
        $this->fakePage('api');
        if (! Cookie::Exists('notice_apidoc')) {
            $this->assign('displayNotice', TRUE);
            $this->register('script_file', 'cookie.min.js');
        }
        break;
        
    case 'translation':
        $api->translation();
        break;
    
    case 'day':
        $api->day($this->request(2));
        break;
    case 'month':
        $api->month($this->request(2));
        break;
    case 'year':
        $api->year($this->request(2));
        break;
    
    case 'authentification':
        $api->authentification($_POST);
        break;
    
    case 'submit':
        $api->submit($_POST);
        break;
        
    case NULL:
        header('Location: '.$this->URL('api/documentation'));
        exit();
        break;
    
    default:
        $api->error(400);
}


?>