<?php
    namespace App\Models;
    class Post extends \Core\Moodel{

        /**
         * 새글을 등록한다.
         * @param $form
         */
        function insertPost($form){
            $this->sql =
                "INSERT INTO POST(
                            POST_TITLE
                            ,POST_CONTENT
                            ,POST_THUMBNAIL
                            ,USER_ID
                            ,ALL_VISIBLE
                            ,SERIES_ID
                            ,TAG
                            ,CREATE_DATE
                        )VALUES (
                            {$form['post_title']}
                            ,{$form['post_content']}
                            ,{$form['post_thumbnail']}
                            ,{$_SESSION["user"]}
                            ,{$form['post_visible']}
                            ,{$form['series_id']}
                            ,{$form['tag']}
                            ,NOW()
                        )";
        }

        /**
         * 글 하나를 가져온다.
         * @param $id
         */
        function selectPost($id){

        }

        /**
         * 글 목록을 최신순으로 가져온다.
         */
        function selectRecentPostList(){

        }
    }