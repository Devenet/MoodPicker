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

namespace Manage;

use DataBase\SQLite;

class ApiRequest {

    const DB = 'moodpicker';
    protected $db;

    protected $id;
    protected $email;
    protected $timestamp;
    protected $api_id;
    protected $approbation;

    protected $is_api_active;

    const ACCEPTED = 1;
    const REJECTED = -1;
    const PENDING = 0;

    public function __construct() {
        $this->db = SQLite::Instance(self::DB);
        $this->id = NULL;
        $this->api_id = NULL;
    }

    public function loadFromId($id) {
        $q = $this->db->prepare('SELECT email, timestamp, api_id, approbation FROM api_request WHERE id = :id');
        $q->execute(array( 'id' => $id ));
        $data = $q->fetch();
        $q->closeCursor();
        if (!empty($data['email'])) {
            $this->id = $id;
            $this->email = $data['email'];
            $this->timestamp = $data['timestamp'];
            $this->api_id = $data['api_id'];
            $this->approbation = $data['approbation'];
        }
    }
    public function loadFromApiId($api_id) {
        $q = $this->db->prepare('SELECT id, email, timestamp, approbation FROM api_request WHERE api_id = :api_id');
        $q->execute(array( 'api_id' => $api_id ));
        $data = $q->fetch();
        $q->closeCursor();
        if (!empty($data['email'])) {
            $this->id = $data['id'];
            $this->email = $data['email'];
            $this->timestamp = $data['timestamp'];
            $this->api_id = $api_id;
            $this->approbation = $data['approbation'];
        }
    }

    public function exists() {
        return !empty($this->id);
    }

    public function getId() {
        return $this->id;
    }

    public function getEmail() {
        return $this->email;
    }
    public function availableEmail($email) {
        $q = $this->db->prepare('SELECT id FROM api_request WHERE email = :email LIMIT 1');
        $q->execute(array( 'email' => htmlspecialchars($email)));
        $data = $q->fetch();
        $q->closeCursor();
        return empty($data['id']);
    }
    public function setEmail($email) {
        $this->email = htmlspecialchars($email);
    }

    public function getApiId($api_id) {
        return $this->api_id;
    }
    public function setApiId($api_id) {
        $this->api_id = $api_id;
    }
    public function isApiActive() {
        if (!$this->exists()) { return false; }
        if (empty($this->is_api_active)) {
            $q = $this->db->prepare('SELECT id FROM credentials WHERE id = :api_id LIMIT 1');
            $q->execute(array( 'api_id' => $this->api_id ));
            $data = $q->fetch();
            $this->is_api_active = !empty($data['id']);
            $q->closeCursor();
        }
        return $this->is_api_active;
    }

    public function getApprobation() {
        if (empty($this->approbation)) { return self::PENDING; }
        return $this->approbation;
    }
    public function setApprobation($approbation) {
        switch($approbation) {
            case self::ACCEPTED:
            case self::REJECTED:
                $this->approbation = $approbation;
                break;

            default:
                $this->approbation = self::PENDING;
        }
    }

    public function save() {
        if ($this->exists()) {
            $q = $this->db->prepare('UPDATE api_request SET approbation = :approbation, api_id = :api_id WHERE id = :id');
            $row_updated = $q->execute(array(
                'approbation' => $this->approbation,
                'api_id' => $this->api_id,
                'id' => $this->id
            ));
            $q->closeCursor();
            return $row_updated == 1;
        }
        if (!empty($this->email)) {
            $q = $this->db->prepare('INSERT INTO api_request(email, timestamp) VALUES (:email, :now)');
            $q->execute(array(
                'email' => $this->email,
                'now' => time()
            ));
            $q->closeCursor();

            $q = $this->db->query('SELECT last_insert_rowid() AS last_row FROM api_request');
            $data = $q->fetch();
            $q->closeCursor();

            $this->id = isset($data['last_row']) ? $data['last_row'] : NULL;
            return $this->exists();
        }
        return false;
    }

    public function delete() {
        if ($this->exists()) {
            $q = $this->db->prepare('DELETE FROM api_request WHERE id = :id');
            $row_updated = $q->execute(array( 'id' => $this->id ));
            $q->closeCursor();
            return $row_updated == 1;
        }
    }

    public static function getRequests() {
        $query = SQLite::Instance(self::DB)->query('SELECT api_request.id AS id, api_request.email AS email, api_request.timestamp as timestamp, api_request.api_id AS api_id, api_request.approbation AS approbation, credentials.api_name AS api_name FROM api_request LEFT JOIN credentials ON api_request.api_id = credentials.id');
        $data = $query->fetchAll();
        $query->closeCursor();
        return $data;
    }

    public static function countPendingRequests() {
      $query = SQLite::Instance(self::DB)->prepare('SELECT count(id) AS count FROM api_request WHERE approbation = :pending');
      $query->execute(array('pending' => self::PENDING));
      $data = $query->fetch();
      $query->closeCursor();
      return $data['count'];
    }

}

?>
