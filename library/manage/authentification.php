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

use Utils\Session;
use Database\File;

class Authentification {

	const SESSION_LOGGED = 'authentification_logged';
	const SESSION_USER = 'authentification_user';
	const SESSION_FAILURE = 'authentification_failure';

    const TIMEOUT_INACTIVITY = 3600;
    const JAIL_DURATION = 14400;
    const MAX_FAILURE_TRY = 3;

    // number of failures
    private $failure;

    public function __construct() {
    	$this->failure = Session::Exists(self::SESSION_FAILURE) ? Session::Get(self::SESSION_FAILURE) : 0;
    }

    public function login($user) {
        Session::Add(self::SESSION_FAILURE, $this->failure = 0);
        Session::Add(self::SESSION_LOGGED, TRUE);
    }

    public function logout() {
        Session::Add(self::SESSION_LOGGED, FALSE);
        Session::Remove(self::SESSION_LOGGED);
    }

    public function isLogged() {
    	return Session::Exists(self::SESSION_LOGGED) && Session::Get(self::SESSION_LOGGED);
    }

    public function isBanned() {
        return $this->failure >= self::MAX_FAILURE_TRY;
    }

    public function addFailure() {
    	Session::Add(self::SESSION_FAILURE, ++$this->failure);
    }
    

}

?>
