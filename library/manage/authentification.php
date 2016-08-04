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

use Utils\Session;
use Database\File;
use Manage\User;


class Authentification {

	const SESSION_LOGGED = 'authentification_logged';
	const SESSION_USER_ID = 'authentification_user_id';
	const SESSION_USER_EMAIL = 'authentification_user_infos';
    const FAILURE_FILE = 'ipbans.php';

    const TIMEOUT_INACTIVITY = 3600;
    const BAN_DURATION = 14400;
    const MAX_TRY = 3;

    private $ffile;
    private $bans;
    private $ip;

    public function __construct() {
        $this->ffile = new File(self::FAILURE_FILE);
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->loadBans();
    }

    private function loadBans() {
        if (!$this->ffile->exists()) {
            $this->ffile->save('<?php'.PHP_EOL.'$bans = '.var_export(array('failures' => [], 'banned' => []), TRUE).';'.PHP_EOL.'?>');
        }
        require $this->ffile->getFile();
        $this->bans = $bans;
    }
    private function saveBans() {
        $this->ffile->save('<?php'.PHP_EOL.'$bans = '.var_export($this->bans, TRUE).';'.PHP_EOL.'?>');
    }

    private function unbanip() {
        unset($this->bans['banned'][$this->ip]);
        unset($this->bans['failures'][$this->ip]);
        $this->saveBans();
    }

    public function login($user_id) {
        $this->unbanip();
				$user = new User();
				$user->loadFromId($user_id);
        Session::Add(self::SESSION_USER_ID, $user_id);
				Session::Add(self::SESSION_USER_EMAIL, $user->getEmail());
        Session::Add(self::SESSION_LOGGED, TRUE);
    }

    public function logout() {
        Session::Add(self::SESSION_LOGGED, FALSE);
        Session::Remove(self::SESSION_USER_ID);
				Session::Remove(self::SESSION_USER_EMAIL);
        Session::Remove(self::SESSION_LOGGED);
    }

    public function isLogged() {
    	return Session::Exists(self::SESSION_LOGGED) && Session::Get(self::SESSION_LOGGED);
    }

    public function isBanned() {
        $banned = isset($this->bans['banned'][$this->ip]);
        if ($banned) {
            // should we deban the IP?
            if ($this->bans['banned'][$this->ip] < time()) {
                $this->unbanip();
                $banned = FALSE;
            }
        }
        return $banned;
    }

    public function addFailure() {
    	if (!isset($this->bans['failures'][$this->ip])) { $this->bans['failures'][$this->ip] = 0; }
        $this->bans['failures'][$this->ip]++;

        if ($this->bans['failures'][$this->ip] > (self::MAX_TRY-1)) {
            $this->bans['banned'][$this->ip] = time() + self::BAN_DURATION;
        }

        $this->saveBans();
    }


}

?>
