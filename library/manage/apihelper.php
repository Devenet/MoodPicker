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

namespace Manage;

use DataBase\SQLite;

class ApiHelper {

    const DB = 'mood_api';
    protected $db;

    private $id;
    private $api_name;
    private $api_key;
    private $api_token;
    private $last_timestamp;
    private $last_ip;
    private $count;

    public function __construct() {
        $this->db = SQLite::Instance(self::DB);
        $this->id = NULL;
    }

    public function loadFromId($id) {
        $q = $this->db->prepare('SELECT api_name, api_key, api_token, last_timestamp, last_ip, count FROM credentials WHERE id = :id');
        $q->execute(array( 'id' => $id ));
        $data = $q->fetch();
        $q->closeCursor();
        if (!empty($data['api_name'])) {
            $this->id = $id;
            $this->api_name = $data['api_name'];
            $this->api_key = $data['api_key'];
            $this->api_token = $data['api_token'];
            $this->last_timestamp = $data['last_timestamp'];
            $this->last_ip = $data['last_ip'];
            $this->count = $data['count'];
        }
    }

    public function exists() {
        return !empty($this->id);
    }

    public function getId() {
        return $this->id;
    }
    public function getApiName() {
        return $this->api_name;
    }
    public function getApiKey() {
        return $this->api_key;
    }
    public function getApiToken() {
        return $this->api_token;
    }
    public function getLastTimestamp() {
        return $this->last_timestamp;
    }
    public function getLastIp() {
        return $this->last_ip;
    }
    public function getCount() {
        return $this->count;
    }

    public function availableApiKey($key) {
        $q = $this->db->prepare('SELECT id FROM credentials WHERE api_key = :key LIMIT 1');
        $q->execute(array( 'key' => htmlspecialchars($key)));
        $data = $q->fetch();
        $q->closeCursor();
        return empty($data['id']);
    }

    public function setApiName($name) {
        $this->api_name = htmlspecialchars($name);
    }
    public function setApiKey($key) {
        $this->api_key = htmlspecialchars($key);
    }
    public function setApiToken($token) {
        $this->api_token = htmlspecialchars($token);
    }

    public function resetCount() {
        $this->count = 0;
    }    

    public function save() {
        if ($this->exists() && !empty($this->api_name)) {
            $q = $this->db->prepare('UPDATE credentials SET api_name = :name, count = :count WHERE id = :id');
            $row_updated = $q->execute(array(
                'name' => $this->api_name,
                'count' => $this->count,
                'id' => $this->id
            ));
            $q->closeCursor();
            return $row_updated == 1;
        }
        if (!empty($this->api_name) && !empty($this->api_key) && !empty($this->api_token)) {
            $q = $this->db->prepare('INSERT INTO credentials(api_name, api_key, api_token) VALUES(:name, :key, :token)');
            $q->execute(array(
                'name' => $this->api_name,
                'key' => $this->api_key,
                'token' => $this->api_token
            ));
            $q->closeCursor();

            $q = $this->db->query('SELECT last_insert_rowid() AS last_row FROM credentials');
            $data = $q->fetch();
            $q->closeCursor();

            $this->id = isset($data['last_row']) ? $data['last_row'] : NULL;
            return $this->exists();
        }
        return false;
    }

    public function delete() {
        if ($this->exists()) {
            // delete generated tokens first
            $q = $this->db->prepare('DELETE FROM tokens WHERE api_id = :id');
            $q->execute(array( 'id' => $this->id ));
            $q->closeCursor();
            // delete api credentials then
            $q = $this->db->prepare('DELETE FROM credentials WHERE id = :id');
            $row_updated = $q->execute(array( 'id' => $this->id ));
            $q->closeCursor();
            return $row_updated == 1;
        }
    }


    public static function getApis() {
        $query = SQLite::Instance(self::DB)->query('SELECT id, api_name, last_timestamp, last_ip, count FROM credentials');
        $data = $query->fetchAll();
        $query->closeCursor();
        return $data;
    }
    
}

?>
