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

class ApiDataBase {

    const DB_API = 'mood_api';
    // 10 min before expiration
    const TOKEN_EXPIRATION = 600;  

    private $db;

    protected $id_credential = NULL;
    
    public function __construct() {
        $this->db = SQLite::Instance(self::DB_API);
    }

    public function acceptCredentials($key, $token) {
        $query = $this->db->query('SELECT id, key, token from credentials');
        $result = FALSE;

        while ($data = $query->fetch()) {
            if ($data['key'] == $key && $data['token'] == $token) {
                $result = TRUE;
                $this->id_credential = $data['id'];
                break;
            }
        }
        $query->closeCursor();
        return $result;
    }


    public function generateToken() {
        $token = array(
            'token' => sha1(uniqid('', TRUE). '_' .mt_rand()),
            'expire' => time() + self::TOKEN_EXPIRATION
        );
        
        $query = $this->db_api->prepare('INSERT INTO tokens(token, expire) VALUES (:token, :expire)');
        $query->execute(array(
            'token' => $token['token'],
            'expire' => $token['expire']
        ));
        $query->closeCursor();
        if (!is_null($this->id_credential)) {
            $query = $this->db_api->prepare('UPDATE credentials SET last_timestamp = :now, last_ip = :ip, WHERE id = :id');
            $query->execute(array(
                'id' => $this->id_credential,
                'last_timestamp' => time(),
                'last_ip' => $_SERVER['REMOTE_ADDR']
            ));
            $query->closeCursor();
        }
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
        $tokens = $this->getTokens();
        $query = $this->db->prepare('DELETE FROM tokens WHERE id = :id');
        $activeTokens = array();

        //remove expired tokens
        for ($i=0; $i<count($tokens); $i++) {
            if (time() > $tokens[$i]['expire']) { $query->execute(array( 'id' => $tokens[$i]['id'] )); }
            else { $activeTokens[] = $tokens[$i]['token']; } 
        }

        $position = array_search($token, $activeTokens);
        $found = $position >= 0 && $position !== FALSE;
        // if accepted remove it
        if ($found) { $query->execute(array( 'id' => $tokens[$position]['id'] )); }
        $query->closeCursor();

        return $found;
    }



}

?>
