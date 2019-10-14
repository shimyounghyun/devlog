<?php
    namespace App\Models;
    class User extends \Core\Model{

        /**
         * 아이디 중복 확인
         * @param $id
         * @return bool true : 사용가능 / false : 사용 불가
         */
        function hasUserID($id){
            $this->sql = "SELECT COUNT(*) AS COUNT FROM USER WHERE USER_ID ='{$id}'";
            return (int)$this->fetch()->COUNT > 0 ? false : true;
        }

        /**
         * 신규 회원을 등록한다.
         * @param $user
         */
        function insertUser($form){
            $this->sql =
                "INSERT INTO USER(
                            USER_ID
                            ,USER_NAME
                            ,USER_EMAIL
                            ,USER_PW)
                        VALUES(
                            '{$form["user_id"]}'
                            ,'{$form["user_name"]}'
                            ,'{$form["email"]}'
                            ,SHA2('{$form["user_pw"]}',256)
                        )";
            $this->query();
        }
    }
?>