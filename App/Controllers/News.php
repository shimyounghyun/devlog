<?php
namespace App\Controllers;

use Core\Controller;
use Core\View;
use App\Models\News as NewsModel;

class News extends Controller{
    var $menu_code = 3;
    /**
     * url : /news
     * 설명 : 해외 뉴스 목록을 보여준다.
     */
    function newsAction(){
        $result['menu_code'] = $this->menu_code;
        $news_model = new NewsModel(); //db처리를 위한 객체 생성
        $result['news_list'] = $news_model->selectNewsList(0); // 뉴스 목록
        $result['news_count'] = $news_model->selectNewsTotalCount(); // 총 개수
        View::renderTemplate("news/news.html",$result);
    }

    /**
     * url : /news/getNewsList
     * 설명 : 뉴스 목록을 가져온다.
     */
    function getNewsListAction(){
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){
                $page_num = $_POST['page_num'];

                // 필수값 확인
                if(!isset($page_num)){
                    $result['result'] = false;
                    return;
                }

                $news_model = new NewsModel(); //db처리를 위한 객체 생성

                $total_count = $news_model->selectNewsTotalCount(); // 총 개수 가져오기

                // 개수 확인
                if($total_count < $page_num * 12){
                    $result['result'] = false;
                    return;
                }

                $result['news_list'] = $news_model->selectNewsList($page_num);
                $result['news_count'] = $total_count;
                $result['result'] = true;
            }
        }catch (Exception $e){
            $result['result'] = false;
            $result['msg'] = "게시글 삭제 중 오류가 발생했습니다.";
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }
}