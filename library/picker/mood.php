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

use Utils\BasicEnum;
use Database\SQLite;

class Mood {
    
    protected $id;
    protected $mood_level;
    protected $timestamp;
    protected $ip;

    public function __construct($mood = MoodLevel::GOOD, $time = NULL, $ip = '0.0.0.0', $id = NULL) {
        $this->mood_level = $mood;
        $this->timestamp = is_null($time) ? time() : $time;
        $this->ip = $ip;
        $this->id = $id;
    }
    
    public function getID() {
        return $this->id;
    }
    
    public function getMood() {
        return (int) $this->mood_level;
    }
    public function isGoodMood() {
        return $this->mood_level == MoodLevel::GOOD;
    }
    
    public function getTime() {
        return (int) $this->timestamp;
    }

    public function getIP() {
        return $this->ip;
    }

    public function save() {
        if (! is_null($this->id)) { return; }
        $db = SQLite::Instance();
        $query = $db->prepare('INSERT INTO mood(mood_level, timestamp, ip) VALUES (:mood, :time, :ip)');
        $query->execute(array(
            'mood' => $this->mood_level,
            'time' => $this->timestamp,
            'ip' => $this->ip
        ));
        $this->id = $db->lastInsertId();
        $query->closeCursor();
    }

    // get all moods 
    static public function AllMoods($mood_level = NULL) {
        $moods = array();
        $db = SQLite::Instance();
        if (is_null($mood_level)) {
            $query = $db->query('SELECT id, mood_level, timestamp, ip FROM mood ORDER BY timestamp DESC');
        } else {
            $query = $db->prepare('SELECT id, mood_level, timestamp, ip FROM mood WHERE mood_level = :mood ORDER BY timestamp DESC');
            $query->execute(array('mood' => $mood_level));
        }
        while ($data = $query->fetch())
            $moods[] = new Mood($data['mood_level'], $data['timestamp'], $data['ip'], $data['id']);
        $query->closeCursor();
        return $moods;
    }
    
    // get moods from a specific year
    static public function YearMoods($year = NULL, $mood_level = NULL) {
        $year = is_null($year) ? date('Y') : $year;
        $moods = array();
        $db = SQLite::Instance();
        $options = array( 'date' => $year );
        if (is_null($mood_level)) {
            $query = $db->prepare("SELECT id, mood_level, timestamp, ip FROM mood WHERE strftime('%Y', timestamp, 'unixepoch', 'localtime') = :date ORDER BY timestamp DESC");
        } else {
            $query = $db->prepare("SELECT id, mood_level, timestamp, ip FROM mood WHERE mood_level = :mood AND strftime('%Y', timestamp, 'unixepoch', 'localtime') = :date ORDER BY timestamp DESC");
            $options['mood'] = $mood_level;
        }
        $query->execute($options);
        while ($data = $query->fetch())
            $moods[] = new Mood($data['mood_level'], $data['timestamp'], $data['ip'], $data['id']);
        $query->closeCursor();
        return $moods;
    }
    
    // get moods from a specific month
    static public function MonthMoods($month = NULL, $year = NULL, $mood_level = NULL) {
        $month = is_null($month) ? date('m') : $month;
        $year = is_null($year) ? date('Y') : $year;
        $moods = array();
        $db = SQLite::Instance();
        $options = array( 'date' => $year.$month );
        if (is_null($mood_level)) {
            $query = $db->prepare("SELECT id, mood_level, timestamp, ip FROM mood WHERE strftime('%Y%m', timestamp, 'unixepoch', 'localtime') = :date ORDER BY timestamp DESC");
        } else {
            $query = $db->prepare("SELECT id, mood_level, timestamp, ip FROM mood WHERE mood_level = :mood AND strftime('%Y%m', timestamp, 'unixepoch', 'localtime') = :date ORDER BY timestamp DESC");
            $options['mood'] = $mood_level;
        }
        $query->execute($options);
        while ($data = $query->fetch())
            $moods[] = new Mood($data['mood_level'], $data['timestamp'], $data['ip'], $data['id']);
        $query->closeCursor();
        return $moods;
    }
    
    // get moods from a specific day
    static public function DayMoods($day = NULL, $month = NULL, $year = NULL, $mood_level = NULL) {
        $day = is_null($day) ? date('d') : $day;
        $month = is_null($month) ? date('m') : $month;
        $year = is_null($year) ? date('Y') : $year;
        $moods = array();
        $db = SQLite::Instance();
        $options = array( 'date' => $year.$month.$day );
        if (is_null($mood_level)) {
            $query = $db->prepare("SELECT id, mood_level, timestamp, ip FROM mood WHERE strftime('%Y%m%d', timestamp, 'unixepoch', 'localtime') = :date ORDER BY timestamp DESC");
        } else {
            $query = $db->prepare("SELECT id, mood_level, timestamp, ip FROM mood WHERE mood_level = :mood AND strftime('%Y%m%d', timestamp, 'unixepoch', 'localtime') = :date ORDER BY timestamp DESC");
            $options['mood'] = $mood_level;
        }
        $query->execute($options);
        while ($data = $query->fetch())
            $moods[] = new Mood($data['mood_level'], $data['timestamp'], $data['ip'], $data['id']);
        $query->closeCursor();
        return $moods;
    }

    // count generator helper
    static private function CountHelper($data) {
        $bads = isset($data[MoodLevel::BAD]) ? $data[MoodLevel::BAD] : 0;
        $goods = isset($data[MoodLevel::GOOD]) ? $data[MoodLevel::GOOD] : 0;
        $count = $bads + $goods; 
        return array(
            MoodLevel::BAD => $bads,
            MoodLevel::GOOD => $goods,
            'count' => $count
        );
    }
    
    // count all moods
    static public function CountAllMoods() {
        $db = SQLite::Instance();
        $query = $db->query('SELECT mood_level, COUNT(*) AS count FROM mood GROUP BY mood_level ORDER BY mood_level');
        $tmp = array();
        while ($data = $query->fetch())
            $tmp[$data['mood_level']] = $data['count'];
        $query->closeCursor();
        return self::CountHelper($tmp);
    }
    
    // count year moods
    static public function CountYearMoods($year = NULL) {
        $year = is_null($year) ? date('Y') : $year;
        $db = SQLite::Instance();
        $query = $db->prepare("SELECT mood_level, COUNT(*) AS count FROM mood WHERE strftime('%Y', timestamp, 'unixepoch', 'localtime') = :date GROUP BY mood_level ORDER BY mood_level");
        $query->execute(array( 'date' => $year ));
        $tmp = array();
        while ($data = $query->fetch())
            $tmp[$data['mood_level']] = $data['count'];
        $query->closeCursor();
        return self::CountHelper($tmp);
    }
    
    // count month moods
    static public function CountMonthMoods($month = NULL, $year = NULL) {
        $month = is_null($month) ? date('m') : $month;
        $year = is_null($year) ? date('Y') : $year;
        $db = SQLite::Instance();
        $query = $db->prepare("SELECT mood_level, COUNT(*) AS count FROM mood WHERE strftime('%Y%m', timestamp, 'unixepoch', 'localtime') = :date GROUP BY mood_level ORDER BY mood_level");
        $query->execute(array( 'date' => $year.$month ));
        $tmp = array();
        while ($data = $query->fetch())
            $tmp[$data['mood_level']] = $data['count'];
        $query->closeCursor();
        return self::CountHelper($tmp);
    }

    // count day moods
    static public function CountDayMoods($day = NULL, $month = NULL, $year = NULL) {
        $day = is_null($day) ? date('d') : $day;
        $month = is_null($month) ? date('m') : $month;
        $year = is_null($year) ? date('Y') : $year;
        $db = SQLite::Instance();
        $query = $db->prepare("SELECT mood_level, COUNT(*) AS count FROM mood WHERE strftime('%Y%m%d', timestamp, 'unixepoch', 'localtime') = :date GROUP BY mood_level ORDER BY mood_level");
        $query->execute(array( 'date' => $year.$month.$day ));
        $tmp = array();
        while ($data = $query->fetch())
            $tmp[$data['mood_level']] = $data['count'];
        $query->closeCursor();
        return self::CountHelper($tmp);
    }
    
    // return years which have moods saved
    static public function YearsAvailable() {
        $years = array();
        $db = SQLite::Instance();
        $query = $db->query("SELECT DISTINCT strftime('%Y', timestamp, 'unixepoch', 'localtime') AS year FROM mood ORDER BY year DESC");
        while ($data = $query->fetch())
            $years[] = $data['year'];
        $query->closeCursor();
        return $years;
    }
    
    // return Months from a year which have moods saved
    static public function MonthsAvailable($year = NULL) {
        $months = array();
        if (is_null($year)) { $year = date('Y'); }
        $db = SQLite::Instance();
        $query = $db->prepare("SELECT DISTINCT strftime('%m', timestamp, 'unixepoch', 'localtime') AS month FROM mood WHERE strftime('%Y', timestamp, 'unixepoch', 'localtime') = :year ORDER BY month DESC");
        $query->execute(array('year' => $year));
        while ($data = $query->fetch())
            $months[] = $data['month'];
        $query->closeCursor();
        return $months;
    }
    
}