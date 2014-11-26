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

namespace Picker;

use Picker\Mood;
use Picker\MoodLevel;
use DataBase\SQLite;
use Core\Config;

class API extends \Core\API {
    
    const VERSION = '1.1.0';

    const API_PATH = "api/v1";
    
    const E_FORMAT_DATE = 'Date format is invalid';
    const E_EMPTY_MOOD = 'Required data for Mood is missing';
    const E_FORMAT_MOOD = 'Mood format is invalid';
    const E_EMPTY_DATA = 'At least one required parameter is missing';

    const P_API_KEY = 'api_key';
    const P_API_TOKEN = 'api_token';
    const P_TOKEN = 'token';
    const P_MOOD = 'mood';

    private $db_api;

    public function __construct() {
        $this->db_api = new ApiDataBase();
    }
    
    protected function checkToken($data) {
        if (! isset($data[self::P_TOKEN])) { $this->error(401, 'Bad token'); }
        if (! $this->db_api->acceptToken($data[self::P_TOKEN])) { $this->error(401, 'Bad token'); }
    }

    /* API RESPONSES */

    public function token($data) {
        if (empty($data)) { $this->error(400); }
        try {
            $data = json_decode($data, true);

            if (! isset($data[self::P_API_KEY]) || empty($data[self::P_API_KEY])) { $this->error(422, self::E_EMPTY_DATA); }
            if (! isset($data[self::P_API_TOKEN]) || empty($data[self::P_API_TOKEN])) { $this->error(422, self::E_EMPTY_DATA); }

            if (! $this->db_api->acceptCredentials($data[self::P_API_KEY], $data[self::P_API_TOKEN])) { $this->error(401, 'Bad credentials'); }

            $token = $this->db_api->generateToken($data[self::P_API_KEY]);
            $this->data['token'] = $token['token'];
            $this->data['expire'] = $token['expire'];

            $this->send();
        }
        catch (\Exception $ex) {
            $this->error(400);
        }
    }

    public function authentification($data) {
        if (empty($data)) { $this->error(400); }
        try {
            $data = json_decode($data, true);

            if (! isset($data[self::P_API_KEY]) || empty($data[self::P_API_KEY])) { $this->error(422, self::E_EMPTY_DATA); }
            if (! isset($data[self::P_API_TOKEN]) || empty($data[self::P_API_TOKEN])) { $this->error(422, self::E_EMPTY_DATA); }

            if (! $this->db_api->acceptCredentials($data[self::P_API_KEY], $data[self::P_API_TOKEN])) { $this->error(401, 'Bad credentials'); }

            $this->data['authentification'] = true;

            $this->send();
        }
        catch (\Exception $ex) {
            $this->error(400);
        }
    }

    public function version() {
        $this->data['api_version'] = self::VERSION;
        $this->data['moodpicker_version'] = \Core\Application::VERSION;
        $this->send();
    }

    public function translation() {
        $this->data['translation'] = MoodLevel::Constants();
        $this->send();
    }
    
    public function month($date = NULL) {
        if (is_null($date)) { $date = date('Y-m'); }
        $date = explode('-', $date);
        
        if (count($date) != 2) { $this->error(422, self::E_FORMAT_DATE); }
        if (! checkdate($date[1], 1, $date[0])) { $this->error(422, self::E_FORMAT_DATE); }
        
        $moods = Mood::MonthMoods($date[1], $date[0]);
        $result = array();
        foreach($moods as $m) {
            $result[] = array (
                'mood' => $m->getMood(),
                'timestamp' => $m->getTime()
            );
        }
        
        $this->data['date'] = $date[0].'-'.$date[1];
        $this->data['moods_count'] = count($result);
        $this->data['moods'] = $result;

        $this->send();
    }
    
    public function year($date = NULL) {
        if (is_null($date)) { $date = date('Y'); }
        $date = explode('-', $date);
        
        if (count($date) != 1) { $this->error(422, self::E_FORMAT_DATE); }
        if (! checkdate(1, 1, $date[0])) { $this->error(422, self::E_FORMAT_DATE); }
        
        $moods = Mood::YearMoods($date[0]);
        $result = array();
        foreach($moods as $m) {
            $result[] = array (
                'mood' => $m->getMood(),
                'timestamp' => $m->getTime()
            );
        }
        
        $this->data['date'] = $date[0];
        $this->data['moods_count'] = count($result);
        $this->data['moods'] = $result;

        $this->send();
    }
    
    public function day($date = NULL) {
        if (is_null($date)) { $date = date('Y-m-d'); }
        //$date = array_map('intval', explode('-', $date));
        $date = explode('-', $date);
        
        if (count($date) != 3) { $this->error(422, self::E_FORMAT_DATE); }
        if (! checkdate($date[1], $date[2], $date[0])) { $this->error(422, self::E_FORMAT_DATE); }
        
        $moods = Mood::DayMoods($date[2], $date[1], $date[0]);
        $result = array();
        foreach($moods as $m) {
            $result[] = array (
                'mood' => $m->getMood(),
                'timestamp' => $m->getTime()
            );
        }
        
        $this->data['date'] = $date[0].'-'.$date[1].'-'.$date[2];
        $this->data['moods_count'] = count($result);
        $this->data['moods'] = $result;
        
        $this->send();
    }

    public function submit($data) {
        if (empty($data)) { $this->error(400); }
        try {
            $data = json_decode($data, true);

            $this->checkToken($data);

            if (! isset($data[self::P_MOOD]) || empty($data[self::P_MOOD])) { $this->error(422, self::E_EMPTY_MOOD); }
            if (! MoodLevel::isValidValue($data[self::P_MOOD]+0)) { $this->error(422, self::E_FORMAT_MOOD); }
            
            $m = new Mood($data[self::P_MOOD] , time(), Config::IP());
            $m->save();

            $this->data['mood'] = $m->getMood();
            $this->data['timestamp'] = $m->getTime();
            
            $this->send();
        }
        catch (\Exception $ex) {
            $this->error(400);
        }
    }

}

?>
