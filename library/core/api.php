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

namespace core;

abstract class API {
    
    const _400 = 'HTTP/1.1 400 Bad Request';
    const _401 = 'HTTP/1.1 401 Unauthorized';
    const _403 = 'HTTP/1.1 403 Forbidden';
    const _404 = 'HTTP/1.1 404 Not Found';
    const _405 = 'HTTP/1.1 405 Method Not Allowed';
    const _422 = 'HTTP/1.1 422 Unprocessable entity';
    const _501 = 'HTTP/1.1 501 Not Implemented';
    const _503 = "HTTP/1.1 503 Service Unavailable";

    protected $data = array();

    protected function send() {
        header('Content-type: application/json');
        exit(json_encode($this->data));
    }
    
    public function error($code = 400, $message = NULL) {
        $this->data['error'] = $code;
        $this->data['message'] = is_null($message) ? trim(preg_replace('#HTTP\/1.1 \d{3}#', '', constant('self::_'.$code))) : $message;
        header(constant('self::_'.$code), true, $code);
        $this->send();
    }
    
}

?>
