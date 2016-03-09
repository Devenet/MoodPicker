# MoodPicker

Share your mood! Mood Picker is a light webapp to know how you are and keep track.  
You can use it only for you, or ask for an entire group's mood.

![Screenshot of Mood Picker](https://github.com/nicolabricot/MoodPicker/raw/master/moodpicker.png)

***

## Installation

### Requirements

Please check that:
* the Apache Rewrite module is enabled
* the PHP SQLite3 extension is installed and enabled

### Get the source

The simplest way —if you have Git installed— is to clone the current repository.

```sh
git clone https://github.com/nicolabricot/MoodPicker moods
```

Otherwise you can download the last version on the [releases page](https://github.com/nicolabricot/MoodPicker/releases), and unzip it as a `moods` folder into your web server root folder.



#### Other path URL

If the webapp is not stored in the `moods` folder on your root web server folder, you have to do one more step.
Let's assume you have installed MoodPicker in `apps/mymood` and you also browse it under `http://mywebsite.tld/apps/mymood`; just edit the `.htaccess` file and update the two following lines:
```
# Replace /moods/ with your path in the 2 following lines
RewriteBase /apps/mymood/
ErrorDocument 403 /apps/mymood/index.php?page=404
```

#### Writable data directory

Be sure that the application have rights to write in the `data` folder.

```sh
chown www-data -R moods/data
```

## Customization

There are few options to customize your Mood Picker.

First create a `config.php` file ont the root folder of the apps, and then edit it.


```php
<?php

$_CONFIG = array(

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

    // debug mode
    'debug' => false
);
```

## API

By default, you share your mood browsing the website.  
An API is available to get or submit moods. Credentials are required to submit moods.

### Credentials

You can manage the credentials from `manage/api`.  
A credential is composed from a couple of key and token.


If you enable _API requests_, visitors will be able to ask for API credentials. You only have to accept or reject the request to allow or forbiden a request.

### Documentation

The API documentation is available from the Manage section. You also can display it for all users.

***

## Screenshots

A list of screenshots is available in the [resources branch](https://github.com/nicolabricot/MoodPicker/blob/resources/screenshots/README.md).

![Screenshot of Mood Picker](https://github.com/nicolabricot/MoodPicker/raw/resources/screenshots/details-2013.png)

## License

Refer you to the [LICENSE file](https://github.com/nicolabricot/MoodPicker/blob/master/LICENSE).

## Want to contribute?

Source code is hosted on [Github](https://github.com/nicolabricot/MoodPicker) by [nicolabricot](http://nicolabricot.com).  
Feel free to fork it and to improve the application!

Let me know if you use Mood Picker by sending me an email, I will be happy ;-)
