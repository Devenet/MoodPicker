<?php

$_CONFIG = array(
    // full URL or path where the website is hosted
    'url' => '/moods',

    // name of the application
    'name' => 'Mood Picker',
    // title used in address bar
    'title' => 'Share your mood!',

    // meta description tag (for search engines)
    'description' => 'Share your mood!',
    // copyright notice in footer
    'copyright' => 'All rights reserved',

    // available themes (match the css file)
    'themes' => array(
        'default',
        'cerulean',
        'cosmo',
        'flatly',
        'lumen',
        'readable',
        'yeti'
    ),
    // force default theme
    'theme' => 'default',

    // api credentials
    // security note: change the example credentials!
    'api' => array(
        // some app
        array(
            'key' => 'a3d0855f89c2aba71141fe458e1736db',
            'token' => 'da39a3ee5e6b4b0d3255bfef95601890afd80709'
        ),
        // another app
        array(
            'key' => 'e767ec939ad15306705d3a6b622cfdfd',
            'token' => '449b1d0eb7c15c9bf0d3da0cf9fe2179f741a509'
        )
    ),

    // debug mode
    'debug' => false
);

?>
