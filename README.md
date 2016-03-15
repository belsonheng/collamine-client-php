# collamine-client-php
Sample client for Collamine in PHP.

To install dependencies
```   
$ composer update
```

To run crawler command
```
$ php artisan crawl http://forums.hardwarezone.com.sg/money-mind --pattern '/^(http:\\/\\/forums\\.hardwarezone\\.com\\.sg\\/money-mind-210\\/)(.*?)\\.html$/i'
```

To start development server
```
$ php artisan serve
```
