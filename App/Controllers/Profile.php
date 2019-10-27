<?php
namespace App\Controllers;

use App\Models\User;
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
        $user = new User();
        $result['user'] = $user->selectProfileUser();

        View::renderTemplate("profile/profile.html", $result);
    }

    function updateDescription(){
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){
                if($_SESSION['USER']->USER_ID == 'syh622'){
                    $user = new User();

                    $form['user_seq'] = $_SESSION['USER']->USER_SEQ;
                    $form['user_description'] = $_POST['user_description'];
                    $user->updateUserDescription($form);

                    $result['result'] = true;
                }
            }
        }catch (Exception $e){
            $result['result'] = false;
            $result['msg'] = "프로필 수정중 오류가 발생했습니다.";
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }
}