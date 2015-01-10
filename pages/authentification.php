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

use Manage\Authentification;
use Manage\User;

if ($this->request(1) == 'logout') {
    $this->auth->logout();
    header('Location: '.$this->URL('manage'));
    exit();
}

$this->canLogin();

if ($this->auth->isLogged()) {
    header('Location: '.$this->URL('manage'));
    exit();
}

if (!empty($_POST)) {
    $this->acceptToken();
    try {
        $this->assign('form_data', array(
            'email' => htmlspecialchars($_POST['email']),
            'remember' => !empty($_POST['remember']) && $_POST['remember'] == 'on'
        ));

        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            throw new \Exception('Please enter a valid email address.');
        if (empty($_POST['pass']))
            throw new \Exception('Please enter a valid password.');

        $u = new User();
        if (! $u->acceptCredential($_POST['email'], $_POST['pass'])) {
            $this->auth->addFailure();
            $this->canLogin();
            throw new \Exception('I’m so sorry but I can’t accept your credential&hellip; Please try again.');
        }

        $u->registerLogin();
        $this->auth->login($u->getId());

        header('Location: '.$this->URL('manage'));
        exit();
    }
    catch (\Exception $e) {
        $this->assign('form_error', $e->getMessage());
    }
}
$this->page('authentification');
$this->getToken();


?>