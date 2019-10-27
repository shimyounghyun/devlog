<?php
    namespace App\Controllers;
    use App\Models\Comment;
    use Core\Controller;
    use Core\View;
    use App\Models\Post AS PostModel;

    class Post extends Controller{

        var $menu_code = 1;

        /**
         * url : /portfolio
         * 설명 : 최근 올라온 포스트 목록을 보여주는 화면
         */
        function portfolioAction(){
            $post = new PostModel();
            $result["portfolio_list"] = $post->selectPortfolioList(0);
            $result["portfolio_count"] = $post->selectPostTotalCount(0);
            $result['menu_code'] = $this->menu_code;
            View::renderTemplate("post/portfolio.html",$result);
        }

        /**
         * url : /deletePost
         * 설명 : 게시글을 삭제한다.
         */
        function deletePostAction(){
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){
                    $post_seq = $_POST['post_num'];

                    $post = new PostModel();
                    $post->deletePost($post_seq);

                    $result['result'] = true;
                }
            }catch (Exception $e){
                $result['result'] = false;
                $result['msg'] = "게시글 삭제 중 오류가 발생했습니다.";
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }

        /**
         * url : /getPortfolioListAction
         * 설명 : 포트폴리오 목록을 반환한다.
         */
        function getPortfolioListAction(){
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){
                    $index = $_POST['page_num'];

                    $post = new PostModel();
                    $total_count = $post->selectPostTotalCount(0);

                    if($index * 12 > $total_count){
                        $result['result'] = false;
                        return;
                    }

                    $result['result'] = true;
                    $result['data'] = $post->selectPortfolioList($index);
                }
            }catch (Exception $e){
                $result['result'] = false;
                $result['msg'] = "목록을 가져오는 중 오류가 발생했습니다.";
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }

        /**
         * url : /write
         * 설명 : 글 작성 화면
         */
        function writeAction(){
            //로그인 정보 확인
            if(!isset($_SESSION['USER']->USER_ID)){
                echo "<script>alert('로그인 후 이용가능합니다.'); history.back();</script>";
                return;
            }

            $result= [];

            // 수정 화면
            if(isset($this->get_params['post_num'])){
                $id = $this->get_params['post_num'];
                $post_model = new PostModel();
                $post = $post_model->selectPost($id,0);

                //작성자 확인
                if(!isset($post) || $post->USER_ID != $_SESSION['USER']->USER_ID){
                    echo "<script>history.back();</script>";
                    return;
                }

                $result['post'] = $post;
            }

            View::renderTemplate("post/write.html", $result);
        }

        /**
         * url : /deleteComment
         * 설명 : 댓글을 삭제 한다.
         */
        function deleteCommentAction(){
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){
                    $comment_model = new Comment(); // 댓글 db

                    $comment_model->deleteComment($_POST['num']);


                    $result['result'] = true;
                }
            }catch (Exception $e){
                $result['result'] = false;
                $result['msg'] = "댓글 삭제 처리중 오류가 발생했습니다.";
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }

        /**
         * url : /view
         * 설명 : 게시글 상세 보기 화면
         */
        function viewAction(){
            $post_seq = $this->get_params['post'];

            $post_model = new PostModel(); // 게시물 db
            $comment_model = new Comment(); // 댓글 db

            //쿠키 가져오기

            // 게시물 상세 정보
            $result_map["post"] = $post_model->selectPost($post_seq,0);

            // 댓글 총 개수
            $result_map["comment_count"] = $comment_model->selectCommentCount($post_seq);

            // 댓글 1페이지 목록
            $result_map["comment_list"] = $comment_model->selectCommentList($post_seq,1);

            // 다른 게시물 목록
            $result_map["other_post_list"] = $post_model->selectOtherPost($post_seq);

            View::renderTemplate("post/view.html", $result_map);
        }

        /**
         * url : /saveComment
         * 설명 : 댓글을 저장한다.
         */
        function saveCommentAction(){
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){
                    $comment_model = new Comment();

                    //댓글 등록
                    $comment_model->insertComment($_POST);

                    $result['result'] = true;

                }
            }catch (Exception $e){
                $result['result'] = false;
                $result['msg'] = "댓글 등록 처리중 오류가 발생했습니다.";
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }

        /**
         * url : /updateComment
         * 설명 : 댓글을 수정한다.
         */
        function updateCommentAction(){
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){
                    $comment_model = new Comment();

                    //댓글 등록
                    $comment_model->updateComment($_POST);

                    $result['result'] = true;

                }
            }catch (Exception $e){
                $result['result'] = false;
                $result['msg'] = "댓글 수정 처리중 오류가 발생했습니다.";
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }

        /**
         * url : /getCommentList
         * 설명 : 댓글 목록을 가져온다.
         */
        function getCommentListAction(){
            try {
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){
                    // parameter 셋팅
                    $post_seq = $_POST["post_seq"]; // 필수 값
                    $page_num = $_POST["page_num"];

                    // 필수값  확인
                    if(!isset($post_seq)){
                        $result['result'] = false;
                        $result['msg'] = "필수값이 누락되었습니다.";
                        return;
                    }

                    $comment_model = new Comment(); // 댓글 DB 처리를 위해 선언

                    // 빈값 또는 값이 없을 경우 0으로 초기화
                    if(!isset($page_num)){
                        $page_num = 0;
                    }

                    // 댓글 총 개수 구하기
                    $comment_total_count = $comment_model->selectCommentCount($post_seq);

                    // 댓글 목록 가져오기
                    $comment_list = $comment_model->selectCommentList($post_seq, $page_num);

                    $result["result"] = true;
                    $result["comment_list"] = $comment_list;
                    $result["comment_count"] = $comment_total_count;
                }
            }catch (Exception $e){
                $result['result'] = false;
                $result['msg'] = "댓글 목록을 가져오는 중 오류가 발생했습니다.";
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }

        /**
         * url : /savePost
         * 설명 : 글을 저장한다.
         */
        function savePostAction(){
            try{
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){ // 새 글 저장
                    $_REQUEST_TYPE = $_POST["request_type"];

                    $post_model = new PostModel(); //DB 저장시 사용

                    //썸네일 저장
                    $file = $this->saveFile($_FILES['post_thumbnail']);

                    // 썸네일 경로 저장
                    $_POST["post_thumbnail"] = $file["url"];

                    //게시글 DB 저장

                    $result['result'] = true;

                    if(isset($_POST['post_seq']) && $_POST['post_seq'] != '' && $_POST['post_seq'] != 0){ //수정
                        $result['msg'] = $post_model->updatePost($_POST);
                    }else{ //등록
                        $result['msg'] = $post_model->insertPost($_POST);
                    }

                }
            }catch (Exception $e){
                $result['result'] = false;
                $result['msg'] = "게시글 등록중 오류가 발생했습니다.";
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }

        /**
         * 이미지 파일 저장
         * @param $FILE
         * @return mixed
         */
        function saveFile($FILE){
            try{
                //첨부파일 유무 체크
                if(!isset($FILE)){
                    $result['result'] = false;
                    $result['msg'] = "업로드된 파일이 없습니다.";
                    return $result;
                }

                if($FILE['error'] > 0) {
                    switch ($FILE['error']) {
                        case 1:
                            // php.ini upload_max_filesize 초과
                            $result['msg'] = "설정된 용량을 초과했습니다.";
                            break;
                        case 2:
                            // form MAX_FILE_SIZE 초과
                            $result['msg'] = "설정된 용량을 초과했습니다.";
                            break;
                        case 3:
                            $result['msg'] = "파일 일부만 업로드 되었습니다.";
                            break;
                        case 4:
                            $result['msg'] = "업로드된 파일이 없습니다.";
                            break;
                        case 5:
                            $result['msg'] = "사용가능한 임시 폴더가 없습니다.";
                            break;
                        case 6:
                            $result['msg'] = "디스크에 저장할 수 없습니다.";
                            break;
                        case 7:
                            $result['msg'] = "파일 업로드가 중지됐습니다.";
                            break;
                        default:
                            $result['msg'] = "시스템 오류가 발생했습니다.";
                            break;
                    }
                    return $result;
                }

                // 업로드 용량 체크
                if($FILE['size'] > 25242880){
                    $result['result'] = false;
                    $result['msg'] = "업로드 용량을 초과했습니다.";
                    return $result;
                }

                //이미지 형식 체크
                $valid = array("jpg", "png", "gif", "bmp", "jpeg");
                $img_path = pathinfo($FILE['name']); //파일명 확인
                $imgext = strtolower($img_path['extension']); //확장자 소문자 처리
                if(!in_array($imgext, $valid)){
                    $result['result'] = false;
                    $result['msg'] = '"jpg", "png", "gif", "bmp", "jpeg" 의 확장자만 가능합니다.';
                    return $result;
                }


                $time = explode(' ',microtime());
                $fileName = $time[1].substr($time[0],2,6).'.'.strtoupper($imgext);
                $filePath = 'upload/editor/';
                $destination_path = getcwd().DIRECTORY_SEPARATOR.$filePath.DIRECTORY_SEPARATOR.basename($fileName);
                if(move_uploaded_file($FILE['tmp_name'],$destination_path)){
                    chmod($destination_path,0777);
                    $result['result'] = true;
                    $result['url'] = _URL.$filePath.$fileName;
                    $result['name'] = $FILE['name'];
                }else{
                    $result['result'] = false;
                }
            }catch(Exception $e){
                $result['result'] = false;
            }finally{
                return $result;
            }
        }

        /**
         * url : /uploadImage
         * 설명 : 이미지 업로드
         */
        function uploadImageAction(){
            try{
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST") {
                    $result = $this->saveFile($_FILES['upload_image']);
                }
            }catch (Exception $e){
                $result['result'] = false;
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }
    }
