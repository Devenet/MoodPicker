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

use Core\Config;
use Manage\Setting;
use Manage\User;
use Manage\ApiHelper;
use Manage\ApiRequest;
use Manage\Authentification;
use Utils\Session;

$this->fakePage('manage');

switch($this->request(1)) {

    case 'installation':
        // verifications
        if (is_file(Config::Path(Config::DIR_DATA.'/installed'))) { break; }

        if (!empty($_POST)) {
            $this->acceptToken();
            try {
                $this->assign('form_data', array(
                    'email' => htmlspecialchars($_POST['email']),
                    'api_display_doc' => !empty($_POST['api_display_doc']) && $_POST['api_display_doc'] == 'on',
                    'api_requests' => !empty($_POST['api_requests']) && $_POST['api_requests'] == 'on'
                ));

                // admin user
                if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                    throw new \Exception('Please enter a valid email address.');
                if (empty($_POST['pass']))
                    throw new \Exception('Please enter a valid password.');
                if (mb_strlen($_POST['pass']) < User::PASSWORD_LENGTH)
                    throw new \Exception('The password must have at least '.User::PASSWORD_LENGTH.' caracters.');
                $u = new User();
                if (!$u->availableEmail($_POST['email']) && empty($_GET['force-erase']))
                    throw new Exception('A user with this email address is already registred.
                    <br /><a href="?force-erase='.urlencode($_POST['email']).'">I want to erase it</a> or no thanks, I will change that.');

                // settings
                $settings = array();
                $settings['api_display_doc'] = !empty($_POST['api_display_doc']);
                $settings['api_requests'] = !empty($_POST['api_requests']);

                // save them
                if ($u->availableEmail($_POST['email'])) { $u->setEmail($_POST['email']); }
                else if (!empty($_GET['force-erase'])) { $u->loadFromEmail(htmlspecialchars($_POST['email'])); }

                $u->setPassword($_POST['pass']);
                if (! $u->save())
                    throw new \Exception('Unable to add the user. Please contact the webmaster.');

                foreach($settings as $key => $value) {
                    $s = new Setting($key);
                    if ($s->getValue() != $settings[$key]) {
                        $s->setValue($value);
                        if (! $s->save())
                            throw new \Exception('Unable to save the new value for '.$key.'.');
                    }
                }

                touch(Config::Path(Config::DIR_DATA.'/installed'));

                header('Location: '.$this->URL('manage'));
                exit();
            }
            catch (\Exception $e) {
                $this->assign('form_error', $e->getMessage());
            }
        }
        else if (!empty($_GET['force-erase'])) {
            $this->assign('form_data', array( 'email' => htmlspecialchars($_GET['force-erase']) ));
        }

        $this->page('manage/installation');
        $this->getToken();

        break;

    case 'api':
        $this->requireAuth();
        switch ($this->request(2)) {

            case 'add':
                if (!empty($_POST)) {
                    $this->acceptToken();
                    try {
                        $this->assign('form_data', array(
                            'api_name' => htmlspecialchars($_POST['api_name']),
                            'api_key' => htmlspecialchars($_POST['api_key']),
                            'api_token' => htmlspecialchars($_POST['api_token'])
                        ));

                        if (empty($_POST['api_name']))
                            throw new \Exception('Please enter a name for the API credentials.');
                        if (empty($_POST['api_key']))
                            throw new \Exception('Please enter an API key.');
                        if (!preg_match('/^[a-z0-9-]{8,}$/', $_POST['api_key']))
                            throw new \Exception('Please enter a valid API key (must be alphanumeric and at least 8 caracters).');
                        if (empty($_POST['api_token']))
                            throw new \Exception('Please enter an API token.');
                        if (!preg_match('/^[a-z0-9-]{8,}$/', $_POST['api_token']))
                            throw new \Exception('Please enter a valid API token (must be alphanumeric and at least 8 caracters).');

                        $api = new ApiHelper();

                        if (!$api->availableApiKey($_POST['api_key']))
                            throw new Exception('An API credentials with this key is already registred.');

                        $api->setApiName($_POST['api_name']);
                        $api->setApiKey($_POST['api_key']);
                        $api->setApiToken($_POST['api_token']);

                        if (! $api->save())
                            throw new \Exception('Unable to add the API credentials. Please contact the webmaster.');

                        header('Location: '.$this->URL('manage/api?created'));
                        exit();
                    }
                    catch (\Exception $e) {
                        $this->assign('form_error', $e->getMessage());
                    }
                }
                else {
                    $this->assign('form_data', array(
                        'api_key' => substr(md5(time()), 0, 25),
                        'api_token' => substr(md5(time()+1), 0, 25)
                    ));
                }
                $this->page('manage/api/add');
                $this->getToken();

                break;

            case 'view':
                if (!$this->request(3)) { break; }

                $api = new ApiHelper();
                $api->loadFromId(intval($this->request(3)));

                if (!$api->exists()) { break; }

                if (!empty($_POST)) {
                    $this->acceptToken();

                    try {
                        $this->assign('form_data', array(
                            'api_name' => $_POST['api_name']
                        ));

                        if (!empty($_POST['update'])) {
                            if (empty($_POST['api_name']))
                                throw new \Exception('Please enter a name for the API credentials.');
                            $api->setApiName($_POST['api_name']);

                            if (! $api->save())
                            throw new \Exception('Unable to add the API credentials. Please contact the webmaster.');

                            header('Location: '.$this->URL('manage/api/view/'.$api->getId().'?updated'));
                            exit();
                        }

                        if (!empty($_POST['reset_count']) && $api->getCount() > 0) {
                            $api->resetCount();
                            if (! $api->save())
                                throw new \Exception('Unable to process the action. Please contact the webmaster.');

                            header('Location: '.$this->URL('manage/api/view/'.$api->getId().'?updated'));
                            exit();
                        }
                    }
                    catch (\Exception $e) {
                        $this->assign('form_error', $e->getMessage());
                    }
                }

                $this->assign('api', $api);
                $this->page('manage/api/view');
                $this->getToken();
                $this->getExtendedToken();
                if (isset($_GET['updated'])) { $this->assign('message', 'The API credentials have been updated.'); }
                break;

            case 'delete':
                if (!$this->request(3) || !$this->request(4)) { break; }
                $this->acceptExtendedToken($this->request(4));

                $api = new ApiHelper();
                $api->loadFromId(intval($this->request(3)));

                if (!$api->exists()) { break; }

                $req = new ApiRequest();
                $req->loadFromApiId($api->getId());

                if (!empty($_POST)) {
                    $this->acceptToken();
                    try {
                        $this->assign('form_data', array(
                            'delete_request' => !empty($_POST['delete_request']) && $_POST['delete_request'] == 'on'
                        ));

                        if (empty($_POST['delete']))
                            throw new \Exception('Nothing will be deleted until you check the box&hellip;');
                        if (empty($_POST['api_id']) || $_POST['api_id'] != intval($this->request(3)))
                            $this->hackAttempt();

                        if (! $api->delete())
                            throw new \Exception('Unable to delete the API credentials. Please contact the webmaster.');

                        $msg = 'deleted';

                        if (! empty($_POST['delete_request'])) {
                            $req = new ApiRequest();
                            $req->loadFromApiId($api->getId());
                            if (! $req->delete()) { $msg = 'partially-deleted'; }
                        }

                        $this->removeExtendedToken($this->request(4));
                        header('Location: '.$this->URL('manage/api?'.$msg));
                        exit();
                    }
                    catch (\Exception $e) {
                        $this->assign('form_error', $e->getMessage());
                    }
                }
                $this->page('manage/api/delete');
                $this->getToken();
                $this->assign('api', array(
                    'id' => $api->getId(),
                    'api_name' => $api->getApiName(),
                    'api_key' => $api->getApiKey(),
                    'api_token' => $api->getApiToken(),
                    'api_request' => $req->exists()
                ));

                break;

            case 'requests':
                switch($this->request(3)) {
                    case 'accept':
                        if (empty($this->request(4)) || empty($this->request(5))) { break; }
                        $this->acceptExtendedToken($this->request(5));

                        $req = new ApiRequest();
                        $req->loadFromId($this->request(4));
                        if (!$req->exists() || $req->getApprobation() != ApiRequest::PENDING) { break; }

                        if (!empty($_POST)) {
                            $this->acceptToken();
                            $api = new ApiHelper();
                            try {
                                $this->assign('form_data', array(
                                    'api_name' => htmlspecialchars($_POST['api_name']),
                                    'api_key' => htmlspecialchars($_POST['api_key']),
                                    'api_token' => htmlspecialchars($_POST['api_token']),
                                    'email' => !empty($_POST['email']) && $_POST['email'] == 'on',
                                    'email_attachement' => !empty($_POST['email_attachement']) && $_POST['email_attachement'] == 'on',
                                    'email_content' => !empty($_POST['email_content']) ? htmlspecialchars($_POST['email_content']) : NULL
                                ));

                                if (empty($_POST['api_name']))
                                    throw new \Exception('Please enter a name for the API credentials.');
                                if (empty($_POST['api_key']))
                                    throw new \Exception('Please enter an API key.');
                                if (!preg_match('/^[a-z0-9-]{8,}$/', $_POST['api_key']))
                                    throw new \Exception('Please enter a valid API key (must be alphanumeric and at least 8 caracters).');
                                if (empty($_POST['api_token']))
                                    throw new \Exception('Please enter an API token.');
                                if (!preg_match('/^[a-z0-9-]{8,}$/', $_POST['api_token']))
                                    throw new \Exception('Please enter a valid API token (must be alphanumeric and at least 8 caracters).');

                                if (!$api->availableApiKey($_POST['api_key']))
                                    throw new Exception('An API credentials with this key is already registred.');

                                $api->setApiName($_POST['api_name']);
                                $api->setApiKey($_POST['api_key']);
                                $api->setApiToken($_POST['api_token']);

                                if (! $api->save())
                                    throw new \Exception('Unable to add the API credentials. Please contact the webmaster.');

                                $req->setApprobation(ApiRequest::ACCEPTED);
                                $req->setApiId($api->getId());

                                if (! $req->save())
                                    throw new \Exception('Unable to add the requested API credentials. Please contact the webmaster.');

                                $this->removeExtendedToken($this->request(5));
                                header('Location: '.$this->URL('manage/api/requests?accepted'));
                                exit();
                            }
                            catch (\Exception $e) {
                                $this->assign('form_error', $e->getMessage());
                                $api->delete();
                            }
                        }
                        else {
                            $this->assign('form_data', array(
                                'api_name' => htmlspecialchars($req->getEmail()),
                                'api_key' => substr(md5(time()), 0, 25),
                                'api_token' => substr(md5(time()+1), 0, 25)
                            ));
                        }
                        $this->assign('email_content', 'Hi '. htmlspecialchars($req->getEmail()) . PHP_EOL.PHP_EOL .'Your request of API credentials for '. $_SERVER['SERVER_NAME'].$this->URL() .' has been approved!'. PHP_EOL .'&bull; API key: [api_key]'. PHP_EOL .'&bull; API token: [api_token]'. PHP_EOL.PHP_EOL .'Thanks for sharing your mood :)');

                        $this->page('manage/api/requests/accept');
                        $this->getToken();
                        break;

                    case 'reject':
                        if (empty($this->request(4)) || empty($this->request(5))) { break; }
                        $this->acceptExtendedToken($this->request(5));

                        $req = new ApiRequest();
                        $req->loadFromId($this->request(4));
                        if (!$req->exists() || $req->getApprobation() != ApiRequest::PENDING) { break; }

                        if (!empty($_POST)) {
                            $this->acceptToken();
                            $api = new ApiHelper();
                            try {
                                $this->assign('form_data', array(
                                    'email' => !empty($_POST['email']) && $_POST['email'] == 'on',
                                    'email_content' => !empty($_POST['email_content']) ? htmlspecialchars($_POST['email_content']) : NULL
                                ));

                                if (empty($_POST['reject']))
                                    throw new \Exception('Nothing will be rejected until you check the box&hellip;');

                                $req->setApprobation(ApiRequest::REJECTED);
                                if (! $req->save())
                                    throw new \Exception('Unable to save the API request. Please contact the webmaster.');

                                $this->removeExtendedToken($this->request(5));
                                header('Location: '.$this->URL('manage/api/requests?rejected'));
                                exit();
                            }
                            catch (\Exception $e) {
                                $this->assign('form_error', $e->getMessage());
                                $api->delete();
                            }
                        }
                        $this->assign('email_content', 'Hi '. htmlspecialchars($req->getEmail()) . PHP_EOL.PHP_EOL .'Your request of API credentials for '. $_SERVER['SERVER_NAME'].$this->URL() .' has been rejected. So sorry!'. PHP_EOL.PHP_EOL .'Thanks for sharing your mood :)');

                        $this->page('manage/api/requests/reject');
                        $this->getToken();
                        $this->assign('request', array( 'email' => $req->getEmail() ));
                        break;

                    case 'remove':
                        if (empty($this->request(4)) || empty($this->request(5))) { break; }
                        $this->acceptExtendedToken($this->request(5));

                        $req = new ApiRequest();
                        $req->loadFromId(intval($this->request(4)));

                        if (!$req->exists()) { break; }

                        if (!empty($_POST)) {
                            $this->acceptToken();
                            try {
                                $this->assign('form_data', array(
                                    'delete_api' => !empty($_POST['delete_api']) && $_POST['delete_api'] == 'on'
                                ));

                                if (empty($_POST['delete']))
                                    throw new \Exception('Nothing will be removed until you check the box&hellip;');
                                if (empty($_POST['request_id']) || $_POST['request_id'] != intval($this->request(4)))
                                    $this->hackAttempt();

                                if (! $req->delete())
                                    throw new \Exception('Unable to remove the API request. Please contact the webmaster.');

                                $msg = 'removed';

                                if ($req->isApiActive() && ! empty($_POST['delete_api'])) {
                                    $api = new ApiHelper();
                                    $api->loadFromId($req->getApiId());
                                    if ($api->delete()) { $msg = 'completely-removed'; }
                                }

                                $this->removeExtendedToken($this->request(5));
                                header('Location: '.$this->URL('manage/api/requests?'.$msg));
                                exit();
                            }
                            catch (\Exception $e) {
                                $this->assign('form_error', $e->getMessage());
                            }
                        }
                        $this->page('manage/api/requests/remove');
                        $this->getToken();
                        $this->assign('request', array( 'id' => $req->getId(), 'email' => $req->getEmail(), 'isApiActive' => $req->isApiActive() ));
                        break;

                    case NULL:
                        $this->page('manage/api/requests');
                        $this->getExtendedToken();
                        $this->assign('requests', ApiRequest::getRequests());
                        if (isset($_GET['accepted'])) { $this->assign('message', 'The API request for credentials has been accepted.'); }
                        else if (isset($_GET['rejected'])) { $this->assign('message', 'The API request has been rejected.'); }
                        else if (isset($_GET['removed'])) { $this->assign('message', 'The API request has been removed.'); }
                        else if (isset($_GET['completely-removed'])) { $this->assign('message', 'The API request and the related API credentials have been deleted.'); }
                        break;
                }
                break;

            case 'settings':
                header('Location: '.$this->URL('manage/settings#api'));
                exit();
                break;

            case NULL:
                $this->page('manage/api');
                $this->getExtendedToken();
                $this->assign('apis', ApiHelper::getApis());
                if (isset($_GET['created'])) { $this->assign('message', 'The API credentials have been created.'); }
                else if (isset($_GET['deleted'])) { $this->assign('message', 'The API credentials have been deleted.'); }
                else if (isset($_GET['partially-deleted'])) { $this->assign('message', 'The API credentials have been deleted even if the related API request could not be deleted.'); }
                break;

        }
        break;

    case 'users':
        $this->requireAuth();
        switch ($this->request(2)) {

            case 'add':
                if (!empty($_POST)) {
                    $this->acceptToken();
                    try {
                        $this->assign('form_data', array(
                            'email' => htmlspecialchars($_POST['email'])
                        ));

                        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                            throw new \Exception('Please enter a valid email address.');
                        if (empty($_POST['pass']))
                            throw new \Exception('Please enter a valid password.');
                        if (mb_strlen($_POST['pass']) < User::PASSWORD_LENGTH)
                            throw new \Exception('The password must have at least '.User::PASSWORD_LENGTH.' caracters.');

                        $u = new User();

                        if (!$u->availableEmail($_POST['email']))
                            throw new Exception('A user with this email address is already registred.');

                        $u->setEmail($_POST['email']);
                        $u->setPassword($_POST['pass']);

                        if (! $u->save())
                            throw new \Exception('Unable to add the user. Please contact the webmaster.');

                        header('Location: '.$this->URL('manage/users?created'));
                        exit();
                    }
                    catch (\Exception $e) {
                        $this->assign('form_error', $e->getMessage());
                    }
                }
                $this->page('manage/users/add');
                $this->getToken();

                break;

            case 'delete':
                if (empty($this->request(3)) || empty($this->request(4))) { break; }
                $this->acceptExtendedToken($this->request(4));

                // can not delete current user
                if (Session::Get(Authentification::SESSION_USER_ID) == intval($this->request(3))) {
                    $this->errorPage('Unable to delete your own account', 'You can not delete yourself. Please ask another administrator to do it!', FALSE);
                }

                $u = new User();
                $u->loadFromId(intval($this->request(3)));

                if (!$u->exists()) { break; }

                if (!empty($_POST)) {
                    $this->acceptToken();
                    try {

                        if (empty($_POST['delete']))
                            throw new \Exception('Nobody will be deleted until you check the box&hellip;');
                        if (empty($_POST['user_id']) || $_POST['user_id'] != intval($this->request(3)))
                            $this->hackAttempt();

                        if (! $u->hasStillUser())
                            throw new \Exception('You can not delete the only remaining user.');

                        if (! $u->delete())
                            throw new \Exception('Unable to delete the user. Please contact the webmaster.');

                        $this->removeExtendedToken($this->request(4));
                        header('Location: '.$this->URL('manage/users?deleted'));
                        exit();
                    }
                    catch (\Exception $e) {
                        $this->assign('form_error', $e->getMessage());
                    }
                }
                $this->page('manage/users/delete');
                $this->getToken();
                $this->assign('user', array( 'id' => $u->getId(), 'email' => $u->getEmail() ));

                break;

            case NULL:
                $this->page('manage/users');
                $this->getExtendedToken();
                $this->assign('users', User::getUsers());
                if (isset($_GET['created'])) { $this->assign('message', 'The user has been created.'); }
                else if (isset($_GET['deleted'])) { $this->assign('message', 'The user has been deleted.'); }
                break;

        }
        break;

    case 'settings':
        $this->requireAuth();
        if (!empty($_POST)) {
            $this->acceptToken();
            try {
                $this->assign('form_data', array(
                    //'conf_email_sender' => $_POST['conf_email_sender'],
                    'api_display_doc' => !empty($_POST['api_display_doc']) && $_POST['api_display_doc'] == 'on',
                    'api_requests' => !empty($_POST['api_requests']) && $_POST['api_requests'] == 'on'
                ));

                $settings = array();
                // text input
                /*
                if (!empty($_POST['conf_email_sender']) && !filter_var($_POST['conf_email_sender'], FILTER_VALIDATE_EMAIL))
                    throw new \Exception('Please enter a valid sender email address.');
                $settings['conf_email_sender'] = htmlspecialchars($_POST['conf_email_sender']);
                */
                // checkbox
                $settings['api_display_doc'] = !empty($_POST['api_display_doc']);
                $settings['api_requests'] = !empty($_POST['api_requests']);

                foreach($settings as $key => $value) {
                    $s = new Setting($key);
                    if ($s->getValue() != $settings[$key]) {
                        $s->setValue($value);
                        if (! $s->save())
                            throw new \Exception('Unable to save the new value for '.$key.'.');
                    }
                }

                header('Location: '.$this->URL('manage/settings?updated'));
                exit();
            }
            catch (\Exception $e) {
                $this->assign('form_error', $e->getMessage());
            }
        }
        else {
            $this->assign('form_data', array(
                'conf_email_sender' => (new Setting('conf_email_sender'))->getValue(),
                'api_display_doc' => (new Setting('api_display_doc'))->getValue(),
                'api_requests' => (new Setting('api_requests'))->getValue()
            ));
        }
        if (isset($_GET['updated'])) { $this->assign('message', 'Settings have been updated.'); }
        $this->page('manage/settings');
        $this->getToken();

        break;

    case NULL:
        $this->requireAuth();
        $this->page('manage/index');
        break;
}


?>
