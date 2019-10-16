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
            //로그인 정보 확인
            if(!isset($_SESSION['USER']->USER_ID)){
                echo "<script>alert('로그인 후 이용가능합니다.'); history.back();</script>";
                return;
            }
            View::renderTemplate("post/write.html");
        }

        /**
         * url : /uploadImage
         * 설명 : 이미지 업로드
         */
        function uploadImageAction(){
            try{
                $method = $_SERVER['REQUEST_METHOD'];
                if($method == "POST"){

                    //첨부파일 유무 체크
                    if(!isset($_FILES['upload_image'])){
                        $result['result'] = false;
                        $result['msg'] = "업로드된 파일이 없습니다.";
                        return;
                    }

                    if($_FILES['upload_image']['error'] > 0) {
                        $result['result'] = false;
                        switch ($_FILES['upload_image']['error']) {
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
                        return;
                    }

                    // 업로드 용량 체크
                    if($_FILES['upload_image']['size'] > 25242880){
                        $result['result'] = false;
                        $result['msg'] = "업로드 용량을 초과했습니다.";
                        return;
                    }

                    //이미지 형식 체크
                    $valid = array("jpg", "png", "gif", "bmp", "jpeg");
                    $img_path = pathinfo($_FILES['upload_image']['name']); //파일명 확인
                    $imgext = strtolower($img_path['extension']); //확장자 소문자 처리
                    if(!in_array($imgext, $valid)){
                        $result['result'] = false;
                        $result['msg'] = '"jpg", "png", "gif", "bmp", "jpeg" 의 확장자만 가능합니다.';
                        return;
                    }


                    $time = explode(' ',microtime());
                    $fileName = $time[1].substr($time[0],2,6).'.'.strtoupper($imgext);
                    $filePath = 'upload/editor/';
                    $destination_path = getcwd().DIRECTORY_SEPARATOR.$filePath.DIRECTORY_SEPARATOR.basename($fileName);
                    if(move_uploaded_file($_FILES['upload_image']['tmp_name'],$destination_path)){
                        chmod($destination_path,0777);
                        $result['result'] = true;
                        $result['url'] = _URL.$filePath.$fileName;
                        $result['name'] = $_FILES['upload_image']['name'];
                    }else{
                        $result['result'] = false;
                    }

                }else{
                    $result['result'] = false;
                }
            }catch (Exception $e){
                $result['result'] = false;
            }finally{
                echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            }
        }
    }
