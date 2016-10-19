<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// cli-config.php
define('APPPATH', dirname(__FILE__) . '/application/');
define('BASEPATH', APPPATH . '/../system/');
define('ENVIRONMENT', 'development');

chdir(APPPATH);
require APPPATH . 'libraries/Doctrine.php';

// Any way to access the EntityManager from  your application
$doctrine = new Doctrine;
$entityManager = $doctrine->em;

return ConsoleRunner::createHelperSet($entityManager);
