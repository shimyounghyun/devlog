<?php
namespace App\Controllers;

use Core\Controller;
use Core\View;
use App\Models\Tag AS TagModel;

class Tag extends Controller{

    var $menu_code = 2;

    /**
     * url : /tag
     * 설명 : 태그 목록 화면
     */
    function tagAction(){
        $result['menu_code'] = $this->menu_code;

        $sort = $this->get_params['sort'];

        $tag_model = new TagModel();
        $result['tag_list'] = $tag_model->selectTagList($sort);
        $result['sort'] = $sort;

        View::renderTemplate("tag/tag.html", $result);
    }

    /**
     * url : /tag/search/{tag:.*}
     * 설명 : 태그 명이 등록된 게시글 목록 가져오기
     */
    function postListByTagAction(){
        $result['menu_code'] = $this->menu_code;

        // 파라미터 확인
        $tag = $this->route_params['tag'];

        // 필수 값 확인
        if(!isset($tag) || trim($tag) == ''){
            echo "<script>history.back();</script>";
            return;
        }

        $tag_model = new TagModel();

        $result['post_count'] = $tag_model->selectPostByTagTotalCount($tag);
        $result['post_list'] = $tag_model->selectPostListByTag($tag, 0);
        $result['post_tag'] = $tag;

        View::renderTemplate("tag/post.html", $result);
    }

    /**
     * url : /tag/getPostList
     * 설명 : 게시물 더 불러오기
     */
    function getPostListAction(){
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            if($method == "POST"){

                $index = $_POST['page_num'];
                $tag_name = $_POST['tag_name'];

                $post = new TagModel();
                $total_count = $post->selectPostByTagTotalCount($tag_name);

                if($index * 12 > $total_count){
                    $result['result'] = false;
                    return;
                }

                $result['result'] = true;
                $result['data'] = $post->selectPostListByTag($tag_name, $index);
            }
        }catch (Exception $e){
            $result['result'] = false;
            $result['msg'] = "목록을 가져오는 중 오류가 발생했습니다.";
        }finally{
            echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
    }
}