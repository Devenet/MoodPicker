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

### Setup the config file

Just copy the `config.default.php` file into `config.php`.

```sh
cp config.default.php config.php
```

That's it.

#### Other path URL

If the webapp is not stored in the `moods` folder on your root web server folder, you have to do two more steps. Let's assume you have installed MoodPicker in `apps/mymood` and you browse it under `http://mywebsite.tld/apps/mymood`. 

1) Edit the `config.php` file to update the URL setting:
```php
'url' => '/apps/mymood'
```
2) Edit the `.htaccess` file and update the two following lines:
```
# Replace /moods/ with your path in the 2 following lines
RewriteBase /apps/mymood/
ErrorDocument 403 /apps/mymood//index.php?page=404
```

## Customization

There are few options to customize your Mood Picker.
Just edit and update your `config.php` file.

```php
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
```

## API

By default, you share your mood browsing the website.  
An API is available to get moods, and also to submit some.

### Credentials

You need to edit the `config.php` file to add credential for each app which need to acces the API.
Do not use the example credentials for security reasons!

### Documentation

The API documentation is available on the API page on your Mood Picker.

***

## Screenshots

A list of screenshots is available in the [resources branch](https://github.com/nicolabricot/MoodPicker/blob/resources/screenshots/README.md).

![Screenshot of Mood Picker](https://github.com/nicolabricot/MoodPicker/raw/resources/screenshots/details-2013.png)

## License

Refer you to the [LICENSE file](https://github.com/nicolabricot/MoodPicker/blob/master/LICENSE).

## Want to contribute?

Source code is hosted on [Github](https://github.com/nicolabricot/MoodPicker) by [nicolabricot](http://nicolabricot.com). Feel free to fork it and to improve the application!

Let me know if you use Mood Picker by sending me an email, I will be happy ;-)
