<?php
namespace App\Controllers;

use Core\View;

class Main extends \Core\Controller {

    /**
     * url : /
     * 설명 : 메인 페이지
     */
    function basicAction(){
        View::render("header.html");
        View::render("main/main.html");
        View::render("footer.html");
    }

    /**
     * url : /policy
     * 설명 : 회원가입 약관 동의 페이지
     */
    function policyAction(){
        View::render("header.html");
        View::render("main/policy.html");
        View::render("footer.html");
    }

    /**
     * url : /auth
     * 설명 : 회원가입 이메일 인증 페이지
     */
    function authAction(){
        View::render("header.html");
        View::render("main/auth.html");
        View::render("footer.html");
    }

    /**
     * url : /regist
     * 설명 : 회원 등록 페이지
     */
    function registAction(){
        session_start();
        $param_code = $this->get_params['code'];
        $session_email = $_SESSION[$param_code];
        if(!isset($param_code) || !isset($session_email)){
            echo "<script>alert('유효한 코드가 아닙니다.'); history.back();</script>";
            return;
        }
        $result_map["code"] = $session_email;
        View::render("header.html");
        View::renderTemplate("main/regist.html",$result_map);
        View::render("footer.html");
    }


    /**
     * url : /email
     * 설명 : 이메일 인증 Ajax
     */
    function emailAction(){
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){
                $email = $_POST["email"];
                if(empty(trim($email))){
                    $result['result'] = false;
                    return;
                }

                // 하루동안 유지
                session_cache_expire(1440);
                session_start();
                $code = $this->generateRandomString();
                $_SESSION[$code] = $email;

                $result['result'] = true;
                $result['code'] = $code;
            }else{
                $result['result'] = fail;
            }
        }catch (Exception $e){
            $result['result'] = fail;
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 랜덤 문자열 생성
     * @param int $length
     * @return string
      */
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}
?>