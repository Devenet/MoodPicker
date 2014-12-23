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

use Picker\API;
use Manage\ApiRequest;
use Manage\Setting;
use Utils\Cookie;
use Utils\TextHelper;

switch($this->request(1)) {

    case substr(API::API_PATH, 4):

        $api = new API();
        switch ($this->request(2)) {

            case 'version':
                $api->version();
                break;
                
            case 'translation':
                $api->translation();
                break;
            
            case 'day':
                $api->day($this->request(3));
                break;
            case 'month':
                $api->month($this->request(3));
                break;
            case 'year':
                $api->year($this->request(3));
                break;
            
            case 'token':
                $api->token(file_get_contents('php://input'));
                break;
            case 'authentification':
                $api->authentification(file_get_contents('php://input'));
                break;
            
            case 'submit':
                $api->submit(file_get_contents('php://input'));
                break;

            default:
                $api->error(400);
        }
        break;

    case 'documentation':
        if (! (new Setting('api_display_doc'))->getValue()) { break; }
        $this->page('api/documentation');
        $this->fakePage('api');
        $this->assign('api_path', API::API_PATH);
        $this->assign('api_request', (new Setting('api_requests'))->getValue());
        if (! Cookie::Exists('notice_apidoc')) {
            $this->assign('displayNotice', TRUE);
            $this->register('script_file', 'cookie.min.js');
        }
        break;

    case 'request':
        if (! (new Setting('api_requests'))->getValue()) { break; }

        $this->fakePage('api');
        switch ($this->request(2)) {
            case 'sent':
                $this->page('api/request_sent');
                break;

            case NULL:
                if (!empty($_POST)) {
                    $this->acceptToken();
                    try {
                        $this->assign('form_data', array(
                            'email' => htmlspecialchars($_POST['email']),
                            'agree' => !empty($_POST['agree']) && $_POST['agree'] == 'on'
                        ));

                        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                            throw new \Exception('Please enter a valid email address.');
                        if (empty($_POST['agree']) || $_POST['agree'] != 'on')
                            throw new \Exception('You have to agree to a fair-use of the API.');

                        $req = new ApiRequest();

                        if (!$req->availableEmail($_POST['email']))
                            throw new \Exception('A user with this email address has already done a request. <br />The webmaster is probably on <abbr title="Maybe just in front of GoT&hellip;" class="tip" data-placement="bottom">vacation</abbr> and can not handle your request for now.');

                        $req->setEmail($_POST['email']);

                        if (!$req->save())
                            throw new Exception('Unable to create your request. Please contact the webmaster.');

                        header('Location: '.$this->URL('api/request/sent'));
                        exit();
                    }
                    catch (\Exception $e) {
                        $this->assign('form_error', $e->getMessage());
                    }
                }
                $this->getToken();
                $this->page('api/request');
                break;
        }
        break;

    case NULL:
        header('Location: '.$this->URL('api/documentation'));
        exit();
        break;
}


?>