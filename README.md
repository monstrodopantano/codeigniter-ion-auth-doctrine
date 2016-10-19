# Codeigniter Ion Auth Doctrine
Adaptation project CodeIgniter-Ion-Auth-2 by autor: Ben Edmunds from framework Doctrine 2 ORM

## Requirements

* PHP 5.4.0 or later
* CodeIgniter 3.x
* Composer

## Install

Just copy the files from this package to the corresponding folder in your application folder. For example, copy Ion_auth/config/ion_auth.php to application/config/ion_auth.php

Installation via Composer

If you like Composer:

```bash
$ cd /path/to/codeigniter/
$ composer install
```

## How to change project

* Permission 777 folder /path/to/codeigniter/application/Proxies

* Change file /path/to/codeigniter/application/autoload.php

Doctrine 2 ORM and About XSS cleaning is that it should only be applied to output, as opposed to input data.
~~~php
<?php
$autoload['libraries'] = array('doctrine');

$autoload['helper'] = array('security');
~~~
