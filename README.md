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

* Create folder and define permission 777 /path/to/codeigniter/application/Proxies

* Change file /path/to/codeigniter/application/autoload.php

Doctrine 2 ORM and About XSS cleaning is that it should only be applied to output, as opposed to input data.

~~~php
<?php
$autoload['libraries'] = array('doctrine');

$autoload['helper'] = array('security');
~~~

* Create you database and set values file in /path/to/codeigniter/application/config/database.php
* Then just run the appropriate SQL file /path/to/codeigniter/application/sql.
* Create Entity Ion Auth with Doctrine 2 ORM

```bash
$ cd /path/to/codeigniter/
$ vendor/bin/doctrine --ansi orm:generate-entities --generate-annotations=true /path/to/codeigniter/application

If success return generating code entity
Processing entity "models\entities\Groups"
Processing entity "models\entities\Users"
Processing entity "models\entities\Users_groups"

Entity classes generated to/path/to/codeigniter/application
```

## Usage by Ion Auth 2

In the package you will find example usage code in the controllers and views folders.
The example code isn't the most beautiful code you'll ever see but it'll show you how to use the library and
it's nice and generic so it doesn't require a MY_controller or anything else.

* Default Login
Username: admin@admin.com Password: password

## Reference

* [Ion Auth 2](https://github.com/benedmunds/CodeIgniter-Ion-Auth)
* [Composer Installation](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx)
* [CodeIgniter](https://github.com/bcit-ci/CodeIgniter)
* [Documentation CodeIgniter](https://codeigniter.com/user_guide/)
* [Doctrine 2 ORM](https://github.com/doctrine/doctrine2)
* [Documentation Doctrine 2](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html)