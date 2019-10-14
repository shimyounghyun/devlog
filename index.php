<?php

define('_ROOT',dirname(__FILE__)."/");
define('_APP',_ROOT."App/");
define('_PUBLIC',_ROOT."public/");
define('_MODEL',_APP."App/Models/");
define('_CONTROLLER',_APP."App/Controllers/");
define('_VIEW',_APP."App/Views/");
$url = str_replace("index.php","","http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}");
define('_URL',$url);
define('_IMG',_URL.'public/img/');
define('_CSS',_URL.'public/common/css/');
define('_JS',_URL.'public/common/js/');

require _ROOT . '/vendor/autoload.php';
$router = new Core\Router();
// Add the routes
$router->add(''         , ['controller' => 'Main', 'action' => 'basic']);
$router->add('email'    , ['controller' => 'Main', 'action' => 'email']);
$router->add('policy'   , ['controller' => 'Main', 'action' => 'policy']);
$router->add('auth'     , ['controller' => 'Main', 'action' => 'auth']);
$router->add('regist'   , ['controller' => 'Main', 'action' => 'regist']);
$router->dispatch($_SERVER['QUERY_STRING']);
?>

