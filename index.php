<?php
define('_LOGO',"https://user-images.githubusercontent.com/26181611/66730506-3f38d780-ee8d-11e9-8c2d-2b84bac69d92.png");
define('_BASIC_THUMB',"https://user-images.githubusercontent.com/26181611/66734736-2f29f380-ee9f-11e9-9953-68ccd5edea7a.jpg");
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

// 로그인
$router->add(''         , ['controller' => 'Main', 'action' => 'basic']);
$router->add('email'    , ['controller' => 'Main', 'action' => 'email']);
$router->add('policy'   , ['controller' => 'Main', 'action' => 'policy']);
$router->add('auth'     , ['controller' => 'Main', 'action' => 'auth']);
$router->add('regist'   , ['controller' => 'Main', 'action' => 'regist']);
$router->add('idCheck'  , ['controller' => 'Main', 'action' => 'idCheck']);
$router->add('join'     , ['controller' => 'Main', 'action' => 'join']);

// 포스트
$router->add('recent'    , ['controller' => 'Post', 'action' => 'recent']);
$router->add('write'     , ['controller' => 'Post', 'action' => 'write']);


$router->dispatch($_SERVER['QUERY_STRING']);
?>

