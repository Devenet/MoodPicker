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

namespace Core;

use Database\SQLite;

class Setting {

    const DB = 'moodpicker';
    const APP_NAME = 'MoodPicker';

    private static $db;
    private static $settings = array(
        'app_id' => 'mood_picker',
        'app_name' => 'Mood Picker',
        'app_title' => 'Share your mood!',
        'app_copyright' => 'Powered by enthusiast people',
        'app_description' => 'Share your mood everyday and track tendances',
        'app_robots' => 'index,follow,noarchive'
    );

    protected $id;
    protected $name;
    protected $value;

    private static function loadDataBase() {
        if (empty(self::$db)) { self::$db = SQLite::Instance(self::DB); }
    }

    public static function DefaultSetting($name) {
      return self::$settings[$name];
    }
    // return a string for robots meta tag
    public static function ParseRobots($index, $follow, $archive) {
    	$result = array(
    		($index ? 'index' : 'noindex'),
    		($follow ? 'follow' : 'nofollow'),
    		($archive ? 'archive' : 'noarchive')
    	);
    	return implode(',', $result);
    }
    // split the robots string used in meta tag
    public static function GetRobots($robots) {
    	$robots = explode(',', $robots);
    	$result = array (
    		'index' => ($robots[0] == 'index'),
    		'follow' => ($robots[1] == 'follow'),
    		'archive' => ($robots[2] == 'archive')
    	);
    	return $result;
    }

    public function __construct($name = NULL) {
        self::loadDataBase();
        $this->id = NULL;

        if (!empty($name)) {
            $this->name = $name;

            $q = self::$db->prepare('SELECT id, value FROM settings WHERE name = :name');
            $q->execute(array( 'name' => $name ));
            $data = $q->fetch();
            $q->closeCursor();

            if (!empty($data['id'])) {
                $this->id = $data['id'];
                $this->value = $data['value'];
            }
        }
    }

    public function exists() {
        return !empty($this->id);
    }

    public function getId() {
        return $this->id;
    }
    public function getName() {
        return $this->name;
    }
    public function getValue() {
        return $this->value;
    }

    public function getValueOrDefault() {
      if (empty($this->value)) {
        return array_key_exists($this->name, Setting::$settings) ? Setting::$settings[$this->name] : NULL;
      }
      return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function save() {
        if ($this->exists()) {
            $q = self::$db->prepare('UPDATE settings SET value = :value WHERE id = :id');
            $row_updated = $q->execute(array(
                'value' => $this->value,
                'id' => $this->id
            ));
            $q->closeCursor();
            return $row_updated == 1;
        }
        if (!empty($this->name) && !empty($this->value)) {
            $q = self::$db->prepare('INSERT INTO settings(name, value) VALUES(:name, :value)');
            $q->execute(array(
                'value' => $this->value,
                'name' => $this->name
            ));
            $q->closeCursor();

            $q = self::$db->query('SELECT last_insert_rowid() AS last_row FROM settings');
            $data = $q->fetch();
            $q->closeCursor();

            $this->id = isset($data['last_row']) ? $data['last_row'] : NULL;

            return $this->exists();
        }
        return false;
    }

    public function delete() {
        if ($this->exists) {
            $q = self::$db->prepare('DELETE FROM settings WHERE id = :id');
            $row_updated = $q->execute(array( 'id' => $this->id ));
            $q->closeCursor();
            return $row_updated == 1;
        }
    }

    public static function getSettings() {
        self::loadDataBase();
        $q = self::$db->query('SELECT id, name, value FROM settings');
        $data = $q->fetchAll();
        $q->closeCursor();
        return $data;
    }

}

?>
