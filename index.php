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

set_include_path('./library');
spl_autoload_extensions('.php');
spl_autoload_register();

use Core\Application;
use Core\Config;
use Utils\Menu;

if (Config::Get('debug')) {
    error_reporting(-1);
    ini_set('display_errors', 1);
} else {
	error_reporting(0);
    ini_set('display_errors', 0);
}

$app = new Application();

$navbar = new Menu();
$navbar->item($app->URL(), 'Share')
       ->item($app->URL('review'), 'Review')
       ->item($app->URL('details'), 'Details');

$navbar_right = new Menu();
/*
$dropdown = new Menu(Menu::DROPDOWN);
$dropdown->item($app->URL('api'), 'API')
         ->item($app->URL('settings'), 'Settings');
$navbar_right->dropdown($dropdown, 'More');
/*/
$navbar_right->item($app->URL('api'), 'API');

$app->register(Menu::NAVBAR, $navbar);
$app->register(Menu::NAVBAR_RIGHT, $navbar_right);

// let's party hard
$app->run();

?>