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

use DataBase\SQLite;
use Core\Config;

class ApiDataBase {

    const DB_API = 'mood_manage';
    // 10 min before expiration
    const TOKEN_EXPIRATION = 600;  

    private $db;
    
    public function __construct() {
        $this->db = SQLite::Instance(self::DB_API);
    }

    public function acceptCredentials($key, $token) {
        $q = $this->db->prepare('SELECT id from credentials WHERE api_key = :key AND api_token = :token');
        $q->execute(array(
            'key' => $key,
            'token' => $token
        ));
        $data = $q->fetch();
        $q->closeCursor();

        if ($data !== false) {
            $q = $this->db->prepare('UPDATE credentials SET last_timestamp = :now, last_ip = :ip, count = count + 1 WHERE id = :id');
            $q->execute(array(
                'id' => $data['id'],
                'now' => time(),
                'ip' => Config::IP()
            ));
            $q->closeCursor();
        }

        return (bool) $data;
    }


    public function generateToken($api_key) {
        $token = array(
            'token' => sha1(uniqid('', TRUE). '_' .mt_rand()),
            'expire' => time() + self::TOKEN_EXPIRATION
        );
        
        $q = $this->db->prepare('INSERT INTO tokens(token, expire, api_key) VALUES (:token, :expire, :api)');
        $q->execute(array(
            'token' => $token['token'],
            'expire' => $token['expire'],
            'api' => $api_key
        ));
        $q->closeCursor();
        return $token;
    }


    protected function getTokens() {
        $tokens = array();
        $query = $this->db->query('SELECT id, token, expire from tokens');
        while ($data = $query->fetch())
            $tokens[] = array( 'token' => $data['token'], 'expire' => $data['expire'], 'id' => $data['id'] );
        $query->closeCursor();
        return $tokens;
    }


    public function acceptToken($token) {
        // remove expired tokens
        $q = $this->db->prepare('DELETE FROM tokens WHERE expire < :now');
        $q->execute(array(
            'now' => time()
        ));
        $q->closeCursor();

        // retrieve token data
        $q = $this->db->prepare('SELECT id from tokens WHERE token = :token');
        $q->execute(array(
            'token' => $token
        ));
        $data = $q->fetch();
        $q->closeCursor();

        // remove if accepted
        if ($data !== false) {
            $q = $this->db->prepare('DELETE FROM tokens WHERE id = :id');
            $q->execute(array(
                'id' => $data['id'],
            ));
            $q->closeCursor();
        }

        return (bool) $data;
    }



}

?>
