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

use Database\SQLite;
use Core\Config;

class User {
    
    const DB = 'moodpicker';
    private $db;

    const PASSWORD_LENGTH = 6;

    protected $id;
    protected $email;
    protected $password;
    protected $last_login;
    protected $last_ip;
    protected $privilege;

    public function __construct() {
        $this->db = SQLite::Instance(self::DB);
        $this->id = NULL;
    }

    public function loadFromId($id) {
        $q = $this->db->prepare('SELECT email, last_login, last_ip FROM users WHERE id = :id');
        $q->execute(array( 'id' => $id ));
        $data = $q->fetch();
        $q->closeCursor();
        if (!empty($data['email'])) {
            $this->id = $id;
            $this->email = $data['email'];
            $this->last_login = $data['last_login'];
            $this->last_ip = $data['last_ip'];
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
    public function getLastLogin() {
        return $this->last_login;
    }
    public function getLastIp() {
        return $this->last_ip;
    }

    public function availableEmail($email) {
        $q = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $q->execute(array( 'email' => htmlspecialchars($email)));
        $data = $q->fetch();
        $q->closeCursor();
        return empty($data['id']);
    }
    public function setEmail($email) {
        $this->email = htmlspecialchars($email);
    }
    public function setPassword($password) {
        if (empty($password) || mb_strlen($password) < self::PASSWORD_LENGTH) { return; }
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function hasStillUser() {
        $q = $this->db->query('SELECT count(id) AS total FROM users');
        $data = $q->fetch();
        $q->closeCursor();
        return $data['total'] > 1;
    }

    public function acceptCredential($email, $password) {
        if (mb_strlen($password) < self::PASSWORD_LENGTH) { return false; }
        $q = $this->db->prepare('SELECT id, password FROM users WHERE email = :email');
        $q->execute(array( 'email' => htmlspecialchars($email)));
        $data = $q->fetch();
        $q->closeCursor();

        if (empty($data['id'])) { return false; }
        if (! password_verify($password, $data['password'])) { return false; }
        
        $this->id = $data['id'];
        return true;
    }

    public function save() {
        if ($this->exists() && !empty($this->password)) {
            $q = $this->db->prepare('UPDATE users SET password = :password WHERE id = :id');
            $row_updated = $q->execute(array(
                'password' => $this->password,
                'id' => $this->id
            ));
            $q->closeCursor();
            return $row_updated == 1;
        }
        if (!empty($this->email) && !empty($this->password)) {
            $q = $this->db->prepare('INSERT INTO users(email, password) VALUES(:email, :password)');
            $q->execute(array(
                'email' => $this->email,
                'password' => $this->password
            ));
            $q->closeCursor();

            $q = $this->db->query('SELECT last_insert_rowid() AS last_row FROM users');
            $data = $q->fetch();
            $q->closeCursor();

            $this->id = isset($data['last_row']) ? $data['last_row'] : NULL;
            return $this->exists();
        }
        return false;
    }

    public function registerLogin() {
        if ($this->exists()) {
            $q = $this->db->prepare('UPDATE users SET last_login = :last_login, last_ip = :last_ip WHERE id = :id');
            $row_updated = $q->execute(array(
                'last_login' => time(),
                'last_ip' => Config::IP(),
                'id' => $this->id
            ));
            $q->closeCursor();
            return $row_updated == 1;
        }
    }

    public function delete() {
        if ($this->exists()) {
            $q = $this->db->prepare('DELETE FROM users WHERE id = :id');
            $row_updated = $q->execute(array( 'id' => $this->id ));
            $q->closeCursor();
            return $row_updated == 1;
        }
    }

    public static function getUsers() {
        $q = SQLite::Instance(self::DB)->query('SELECT id, email, last_login FROM users ORDER BY id ASC');
        $result = $q->fetchAll();
        $q->closeCursor();
        return $result;
    }

}

?>