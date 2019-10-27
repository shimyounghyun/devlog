<?php
namespace App\Controllers;

use App\Models\User;
use Core\Controller;
use Core\View;
use mysql_xdevapi\Exception;

class Main extends Controller {

    /**
     * url : /
     * 설명 : 메인 페이지
     */
    function basicAction(){
        unset($_SESSION['USER']); //회원 로그아웃 처리
        View::renderTemplate("main/main.html");
    }

    /**
     * url : /search
     * 설명 : 검색 화면
     */
    function searchAction(){
        $query = $this->get_params['query'];
        $result = [];


        if(isset($query)){
            $post_model = new \App\Models\Post();
            $result['query'] = addslashes($query);
            $result['post_list'] = $post_model->selectSearchPostList(addslashes($query), 0);
            $result['post_count'] = $post_model->selectSearchPostCount(addslashes($query));
        }

        View::renderTemplate("main/search.html",$result);
    }

    /**
     * url : /policy
     * 설명 : 회원가입 약관 동의 페이지
     */
    function policyAction(){
        View::renderTemplate("main/policy.html");
    }

    /**
     * url : /auth
     * 설명 : 회원가입 이메일 인증 페이지
     */
    function authAction(){
        View::renderTemplate("main/auth.html");
    }

    /**
     * url : /postBySearchText
     * 설명 : 문자열로 포스트 검색
     */
    function postBySearchTextAction(){
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){
                $post_model = new \App\Models\Post();
                $text = $_POST['input_text'];
                $page_num = $_POST['page_num'];

                $result['result'] = true;
                $result['post_list'] = $post_model->selectSearchPostList($text, $page_num);
                $result['post_count'] = $post_model->selectSearchPostCount($text);
            }
        }catch(Exception $e){
            $result['result'] = false;
            $result['msg'] = "로그인 처리중 오류가 발생했습니다.";
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * url : /login
     * 설명 : 회원 아이디/비밀번호를 확인 후 세션에 저장한다.
     */
    function loginAction(){
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){
                $user = new User(); // db 조회를 위한 모델 생성
                $data = $user->selectUser($_POST); // 회원 정보 조회

                if(isset($data) && isset($data->USER_ID) ){
                    $_SESSION['USER'] = $data;
                    $result['result'] = true;
                }else{
                    $result['result'] = false;
                    $result['msg'] = "일치하는 회원정보가 없습니다.";
                }

            }
        }catch(Exception $e){
            $result['result'] = false;
            $result['msg'] = "로그인 처리중 오류가 발생했습니다.";
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * url : /regist
     * 설명 : 회원 등록 페이지
     */
    function registAction(){
        $param_code = $this->get_params['code'];
        $session_email = $_SESSION[$param_code];
        if(!isset($param_code) || !isset($session_email)){
            echo "<script>alert('유효한 코드가 아닙니다.'); history.back();</script>";
            return;
        }
        $result_map["code"] = $session_email;
        View::renderTemplate("main/regist.html",$result_map);
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

                $user = new User(); // db 조회를 위한 모델 생성
                $data = $user->hasEmail($email); // 해당 이메일로 중복 결과 반환

                if(!$data){ //중복 이메일일 경우
                    $result['result'] = false;
                    $result['msg'] = "이미 인증완료된 이메일입니다.";
                    return;
                }

                // 하루동안 유지
//                session_cache_expire(1440);
                $code = $this->generateRandomString();
                $_SESSION[$code] = $email;

                $result['result'] = true;
                $result['code'] = $code;
            }else{
                $result['result'] = false;
            }
        }catch (Exception $e){
            $result['result'] = false;
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * url : /idCheck
     * 설명 : 아이디 중복 확인 Ajax
     */
    function idCheckAction(){
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){
                $id = $_POST["id"]; // 사용자 입력 아이디
                $user = new User(); // db 조회를 위한 모델 생성
                $data = $user->hasUserID($id); // 해당 아이디로 중복 결과 반환

                $result['result'] = true;
                if($data){
                    $result['msg'] = "사용 가능한 아이디입니다.";
                }else{
                    $result['msg'] = "이미 사용중인 아이디입니다.";
                }
                $result['data'] = $data;
            }else{
                $result['result'] = false;
                $result['msg'] = "처리에 실패했습니다.";
            }
        }catch (Exception $e){
            $result['result'] = false;
            $result['msg'] = "회원 아이디 중복 검사중 오류가 발생했습니다.";
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * url : /join
     * 설명 : 회원 데이터를 받아 db를 저장한다.
     */
    function joinAction(){
        try{
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){
                $user = new User(); // db 조회를 위한 모델 생성
                $user->insertUser($_POST);

                $login_user = $user->selectUser($_POST);
                if(isset($login_user) && isset($login_user->USER_ID)){
                    $_SESSION['USER'] = $login_user;
                    $result['result'] = true;
                }else{
                    $result['false'] = true;
                    $result['msg'] = "회원 등록에 실패했습니다.";
                }
            }else{
                $result['result'] = false;
                $result['msg'] = "처리에 실패했습니다.";
            }
        }catch (Exception $e){
            $result['result'] = false;
            $result['msg'] = "회원 등록 중 오류가 발생했습니다.";
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
