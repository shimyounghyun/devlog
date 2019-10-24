<?php
namespace App\Controllers;

use Core\Controller;
use Core\View;

class Profile extends Controller{

    var $menu_code = 0;

    /**
     * url : /profile
     * 설명 : 프로필 화면
     */
    function profileAction(){
        $result['menu_code'] = $this->menu_code;

        View::renderTemplate("profile/profile.html", $result);
    }
}