<?php
    namespace App\Models;
    class User extends \Core\Model{

        function updateUserDescription($form){
            $this->sql = "
                UPDATE
                    USER
                SET
                    USER_DESCRIPTION = :user_description
                WHERE
                    USER_SEQ = :user_seq
            ";

            $this->column = [
              ':user_description'=>$form['user_description']
                ,':user_seq'=>$form['user_seq']
            ];

            $this->query();
        }

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
         * 이메일 중복 확인
         * @param $email
         * @return bool
         */
        function hasEmail($email){
            $this->sql = "SELECT COUNT(*) AS COUNT FROM USER WHERE USER_EMAIL ='{$email}' AND USER_DELETE = FALSE";
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
                            ,PASSWORD('{$form["user_pw"]}')
                        )";
            $this->query();
        }

        /**
         * 회원 조회를 한다.
         * @param $form
         */
        function selectUser($form){
            $this->sql =
                "SELECT
                        USER_ID
                        ,USER_NAME
                        ,USER_EMAIL
                        ,USER_SEQ
                 FROM
                        USER
                 WHERE
                        USER_ID =:user_id
                        AND USER_PW = PASSWORD(:user_pw)
                        AND USER_DELETE = FALSE";
            $this->column = [
              ':user_id'=>$form['user_id']
              ,':user_pw'=>$form['user_pw']
            ];
            return $this->fetch();
        }

        function selectProfileUser(){
            $this->sql =
                "SELECT
                        USER_ID
                        ,USER_NAME
                        ,USER_EMAIL
                        ,USER_SEQ
                        ,USER_DESCRIPTION
                        ,USER_THUMBNAIL
                 FROM
                        USER
                 WHERE
                        USER_ID = 'syh622'
                        ";
            return $this->fetch();
        }
    }
?>