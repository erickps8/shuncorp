<?php
define( 'BASE_URL' , '/' );
define( 'baseSistema' , '/public/sistema' );
/* define( 'BaseImg' , '/home/shuncorp/www/public/images/' );
define( 'basePublic' , '/home/shuncorp/www/public' ); */
define( 'BaseImg' , ' /home/shuncorp/public_html/public/images/' );
define( 'basePublic' , '/home/shuncorp/public_html/public' );

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
 
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../library/PHPExcel/Classes'),
    realpath(APPLICATION_PATH . '/../library/mysqldump/src/Ifsnop/Mysqldump'),
    realpath(APPLICATION_PATH . '/modules/admin/models'),
    realpath(APPLICATION_PATH . '/modules/admin/models/bo'),
    realpath(APPLICATION_PATH . '/modules/admin/models/tables'),
    realpath(APPLICATION_PATH . '/modules/default/models'),
    realpath(APPLICATION_PATH . '/modules/default/models/bo'),
    realpath(APPLICATION_PATH . '/modules/default/models/tables'),
    get_include_path()
)));


//include_once "Zend/Loader.php";
//Zend_Loader::registerAutoload();

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

/** Zend_Application */
require_once 'Zend/Application.php';

Zend_Layout::startMvc(
	array(
		'layoutPath' => '../application/layouts',
		'layout' => 'sistemapadrao'
	)
);

Zend_Session::start();
$sessao = new Zend_Session_Namespace('Idiomas');

$translate	= new Zend_Translate('gettext','idiomas/en.mo','en');
$translate->getAdapter()->addTranslation('idiomas/zh.mo','zh');
$translate->getAdapter()->addTranslation('idiomas/pt_BR.mo','pt_BR');
$translate->getAdapter()->addTranslation('idiomas/es.mo','es');

if(empty($sessao->idioma)):
	$translate->getAdapter()->setLocale('pt_BR');
else:
	$translate->getAdapter()->setLocale($sessao->idioma);
endif;

$registry	= Zend_Registry::getInstance();
$registry->set("translate",$translate);

$registry->set("pastaPadrao","/home/shuncorp/public_html/");
$registry->set("conexaoDb", array ('host' => 'mysql01.shuncorp.hospedagemdesites.ws', 'username' => 'shuncorp', 'password' => 'BdHbrNew2020', 'dbname' => 'shuncorp', 'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;')));
//$registry->set("conexaoDb", array ('host' => 'mysql.shuncorp.com', 'username' => 'shuncorp', 'password' => 'BdMySql2008', 'dbname' => 'shuncorp', 'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8;')));

$registry->set("mailSmtp", array ('ssl' => 'tls'));

$sessao = new Zend_Session_Namespace('Logado');
$sessao->setExpirationSeconds(1200);

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.xml'
);

$application->bootstrap()
            ->run();
