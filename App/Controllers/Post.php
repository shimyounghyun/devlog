<?php
    namespace App\Controllers;

    use Core\View;

    class Post extends \Core\Controller{

        /**
         * url : /recent
         * 설명 : 최근 올라온 포스트 목록을 보여주는 화면
         */
        function recentAction(){
            View::renderTemplate("post/recent.html");
        }

        /**
         * url : /write
         * 설명 : 글 작성 화면
         */
        function writeAction(){
            View::renderTemplate("post/write.html");
        }
    }
