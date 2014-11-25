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

class ApiRequest {

    const DB = 'mood_manage';
    protected $db;

    protected $id;
    protected $email;
    protected $timestamp;
    protected $api_id;
    protected $approbation;

    public function __construct() {
        $this->db = SQLite::Instance(self::DB);
        $this->id = NULL;
    }

    public function setEmail($email) {
        $this->email = $email;
    }
    public function setApprobation($approbation) {
        $this->approbation = $approbation;
    }

    public function create() {
        if (empty($this->email)) { return false; }

        $q = $this->db->prepare('INSERT INTO api_request(email, timestamp) VALUES (:email, :now)');
        $q->execute(array(
            'email' => $this->email,
            'now' => time()
        ));
        $q->closeCursor();

        $q = $this->db->query('SELECT last_insert_rowid() AS last_row FROM api_request');
        $data = $q->fetch();
        $q->closeCursor();

        return isset($data['last_row']) ? $data['last_row'] : false;
    }

}

?>
