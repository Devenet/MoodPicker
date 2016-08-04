# MoodPicker

Share your mood! Mood Picker is a light webapp to know how you are and keep track.  
You can use it only for you, or ask for an entire group’s mood.

![Screenshot of Mood Picker](https://github.com/Devenet/MoodPicker/raw/master/moodpicker.png)

***

## Installation

Please check that the Apache _Rewrite module_ and the PHP _SQLite3 extension_ are installed and enabled on your server.

### Get the source

The simplest way —if you have Git installed— is to clone the current repository.

```sh
git clone https://github.com/Devenet/MoodPicker moods
```

Otherwise you can download the last version on the [releases page](https://github.com/Devenet/MoodPicker/releases), and unzip it as a `moods` folder into your web server root folder.

‣ Be sure that the application have rights to write in the `data` folder.

#### Other path URL

If the webapp is not stored in the `moods` folder on your root web server folder, you have to do one more step.
Let's assume you have installed MoodPicker in `apps/mymood` and you also browse it under `http://mywebsite.tld/apps/mymood`; just edit the `.htaccess` file and update the two following lines:
```
RewriteBase /apps/mymood/
ErrorDocument 403 /apps/mymood/index.php?page=404
```

## Customization

To offer different themes or to enable _debug_ information, create a `config.php` file on the root folder of the app, with the following lines:

```php
<?php

$_CONFIG = array(

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

    // debug mode
    'debug' => false
);
```

## API credentials and documentation

An API is available on each instance to send a mood or get data.

The documentation is always accessible when logged. You can also enable public API documentation page for everyone.  
API credentials are managed from the administration when logged; you can offer a public API credentials request page.

## Want to contribute?

Source code is hosted on [Github](https://github.com/Devenet/MoodPicker) by [Nicolas Devenet](http://nicolas.devenet.info).

Feel free to fork it and to improve the application!  
Let me know if you use Mood Picker by sending me an email, I will be happy ;-)

***

## Screenshots

A list of screenshots is available in the [resources branch](https://github.com/Devenet/MoodPicker/blob/resources/screenshots/README.md).

![Screenshot of Mood Picker](https://github.com/Devenet/MoodPicker/raw/resources/screenshots/details-2013.png)
