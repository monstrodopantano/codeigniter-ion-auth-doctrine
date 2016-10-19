<?php
include_once APPPATH . '../vendor/autoload.php';

use Doctrine\Common\ClassLoader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

/**
 * Doctrine bootstrap library for CodeIgniter with HMVC
 *
 * @author	Sandro C. Oliveira <sandro.webdesigner@gmail.com>
 */
class Doctrine
{

	public $em = null;
	public $tool = null;

	public function __construct()
	{
        Setup::registerAutoloadDirectory(APPPATH . '../vendor/doctrine/common/lib');

        // With this configuration, your model files need to be in application/models/Entity
        // e.g. Creating a new Entity\User loads the class from application/models/Entity/User.php
        $models_namespace = 'models\entities';
        $models_path = APPPATH;
        $proxies_dir = APPPATH . 'Proxies';
        $metadata_paths = array(APPPATH . 'models', APPPATH . 'schema');

        $loader = new ClassLoader($models_namespace, $models_path);
		$loader->register();

        // Set $dev_mode to TRUE to disable caching while you develop
        if (ENVIRONMENT == 'development')
            $isDevMode = true;
        else
            $isDevMode = false;

        // If you want to use a different metadata driver, change createAnnotationMetadataConfiguration
        // to createXMLMetadataConfiguration or createYAMLMetadataConfiguration.
		try{
//			$config = Setup::createAnnotationMetadataConfiguration($metadata_paths, $isDevMode, $proxies_dir);
            $config = Setup::createYAMLMetadataConfiguration($metadata_paths, $isDevMode, $proxies_dir);

//            $driver = new YamlDriver($metadata_paths);
//            $config->setMetadataDriverImpl($driver);

			$this->em = EntityManager::create($this->connectionOptionsDatabaseCodeIgniter(), $config);
            $this->tool = new SchemaTool($this->em);

            if (ENVIRONMENT == 'development') {
//                $logger = new Doctrine\DBAL\Logging\EchoSQLLogger;
//                $config->setSqlLogger($logger);
            }
		}catch(Exception $e){

			if (ENVIRONMENT == 'development') {

				var_dump($e->getMessage());
			}else{
				show_error('SISTEMA SEM CONEXÃO COM BANCO DE DADOS');
				log_message('error', $e->getMessage());
			}
			die();
		}
    }

    private function connectionOptionsDatabaseCodeIgniter(){

		// Load the database configuration from CodeIgniter
		require APPPATH . 'config/database.php';

        if( $db['default']['dbdriver'] === 'postgre' )
            $db['default']['dbdriver'] = 'pdo_pgsql';
        elseif( $db['default']['dbdriver'] === 'mysql' )
            $db['default']['dbdriver'] = 'pdo_mysql';
        elseif( $db['default']['dbdriver'] === 'oci8' )
            $db['default']['dbdriver'] = 'pdo_oci';
        elseif( $db['default']['dbdriver'] === 'odbc' )
            $db['default']['dbdriver'] = 'pdo_odbc';
        elseif( $db['default']['dbdriver'] === 'sqlite' )
            $db['default']['dbdriver'] = 'pdo_sqlite';

		$connection_options = array(
			'driver'		=> $db['default']['dbdriver'],
			'user'			=> $db['default']['username'],
			'password'		=> $db['default']['password'],
			'host'			=> $db['default']['hostname'],
			'dbname'		=> $db['default']['database'],
			'charset'		=> $db['default']['char_set'],
			'driverOptions'	=> array(
				'charset'	=> $db['default']['char_set'],
				'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
			),
		);

        return $connection_options;
    }
}