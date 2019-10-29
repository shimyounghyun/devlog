<?php


namespace App\Controllers;
use App\Models\News;
use Core\View;

use Core\Controller;
include 'simple_html_dom.php';

class Curl extends Controller{

    /**
     * url : /crawl
     * 설명 : 해외 it관련 사이트 크롤링
     */
    function crawlNewsHTMLAction(){
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "POST"){ // 포스트 방식 확인

            // 헤더 암호 확인
            $header = apache_request_headers();
            if($header['Auth'] != "shimyoung62"){
                echo '<script>history.back();</script>';
                return;
            }

            $news_model = new News();

            // -------------------------
            // https://opensource.com
            // -------------------------
            $curl = $this->getPage('https://opensource.com');
            $dom = new \simple_html_dom($curl);
            foreach ($dom->find('#content > div.panel-pane.pane-views-panes.pane-homepage-articles-panel-pane-recent > div > div.view-content') as $element){
                foreach ($element->find('div.panelizer-view-mode.node.node-teaser-wide.node-article.node-promoted.node-promoted') as $key=>$val){
                    $form['origin_title'] = trim($val->find('.pane-node-title',0)->plaintext);
                    $form['origin_sub_title'] = trim($val->find('div.panel-pane.pane-token.pane-node-field-article-subhead',0)->plaintext);
                    $form['news_writer'] = "Opensource.com";
                    $form['news_date'] = trim($val->find('.byline__date',0)->plaintext);
                    $form['news_link'] = trim('https://opensource.com'.$val->find('div.panel-pane.pane-node-title > h2 > a',0)->href);
                    $form['news_thumb'] = trim($val->find('div.clearfix > div.teaser-wide__image > div > div > div > div > a > img',0)->src);
                        if(!$news_model->hasNews($form)){
                            $news_model->insertNews($form);
                        }
                }
            }

            // -------------------------
            // https://sdtimes.com/
            // -------------------------
            $curl = $this->getPage('https://sdtimes.com');
            $dom = new \simple_html_dom($curl);
            $form = [];
            foreach ($dom->find('#latestnews > div:nth-child(1) > div:nth-child(3)') as $element){
                foreach ($element->find('.latestnewsitem.col-md-6') as $key=>$val){
                    $form['origin_title'] = trim($val->find('.col-lg-8.col-md-7.col-sm-9.col-xs-12 h4',0)->plaintext); //제목
                    $form['origin_sub_title'] = trim($val->find('.col-lg-8.col-md-7.col-sm-9.col-xs-12 p',0)->plaintext);
                    $form['news_link'] = trim($val->find('.col-lg-8.col-md-7.col-sm-9.col-xs-12 h4 a',0)->href); // 링크
                    $form['news_writer'] = 'SD times';
                    $form['news_thumb'] = trim($val->find('div.img-wrap > a > img',0)->src);
                    if(!$news_model->hasNews($form)){
                        $news_model->insertNews($form);
                    }
                }
            }

            // -------------------------
            // https://techcrunch.com
            // -------------------------
            $curl = $this->getPage('https://techcrunch.com');
            $dom = new \simple_html_dom($curl);
            $form = [];
            foreach ($dom->find('#root > div > div > div > div') as $element){
                foreach ($element->find('.post-block.post-block--image.post-block--unread') as $key=>$val){
                    $form['origin_title'] = trim($element->find('header > h2',$key)->plaintext); //제목
                    $form['origin_sub_title'] = trim($element->find('.post-block__content',$key)->plaintext); //부제목
                    $form['news_link'] = trim($element->find('header > h2 > a',$key)->href); //링크
                    $form['news_date'] = trim($element->find('header > div > div > time',$key)->plaintext); //날짜
                    $form['news_writer'] = 'TechCrunch';
                    $form['news_thumb'] = trim($element->find('figure > a > img',$key)->src);
                    if(!$news_model->hasNews($form)){
                        $news_model->insertNews($form);
                    }
                }
            }


            // 번역이 안된 목록 가져오기
            $news_list = $news_model->selectNonTransTitleNewsList();

            // 번역할 제목들을 개행 문자 \r\n를 넣어 합친다. -> api 호출 최소화를 위해
            $query = [];
            foreach($news_list as $key=>$val){
                $query[$key] = $val->ORIGIN_TITLE;
            }
            $query = join("\r\n",$query);

            // 번역 api를 호출한다.
            $trans_list = $this->getKakaoTrans($query)->translated_text;

            // 번역이 완료되면 다시 목록에 집어 넣는다.
            // db에 다시 저장한다.
            for($i = 0; $i < count($news_list); $i++){
                $news_list[$i]->trans_title = $trans_list[$i][0];
                $form['news_seq'] = $news_list[$i]->NEWS_SEQ;
                $form['trans_title'] = $trans_list[$i][0];
                $news_model->updateNews($form);
            }
        }else{
            echo '<script>history.back();</script>';
        }
    }

    function getKakaoTrans($query){
        $url = 'https://kapi.kakao.com/v1/translation/translate';


//        $headers = [
//            'Authorization: KakaoAK c7524188593657dd7f9c6ec2110ac770'
//        ];
        $headers = [
            'Authorization: KakaoAK 0124baa2fc36fff2d9d2f09075658044'
        ];

        $postData = [
            'src_lang'    => 'en',
            'target_lang' => 'kr',
            'query'       => $query
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

        $json = curl_exec($ch);

        if ($errno = curl_errno($ch)) {
            $result = new \stdClass;
            $result->errno = $errno;
            $result->error = 'Curl error: ' . curl_error($ch);
        } else {
            $response = json_decode($json);
            $result = $response;
        }
        return $result;
    }

    // 페이지 크롤링 함수 (페이지주소, post값, 리퍼러, 헤더포함? (포함시 y), 쿠기파일 생성경로)
    function getPage($pageURL, $post=array(), $referer="", $header="", $cookieURL=""){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pageURL);

        if ($referer != "") {
            $referer = getSiteHost($referer);
            curl_setopt($ch, CURLOPT_REFERER, $referer . "/");
        }

        if ($header == "y") {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if (count($post) > 0) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        if ($cookieURL != "") {
            // 쿠키가 필요하다면, 쿠키 생성 폴더 검사 및 없을시 생성 과정도 포함하는게 좋을 듯.
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieURL);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieURL);
        }

        $result = curl_exec ($ch);
        curl_close($ch);

        return $result;
    }

    // 정확한 호스트를 얻기위해 편의상 쓰는 함수
    function getSiteHost($pageURL) {
        $siteParts = parse_url($pageURL);
        return $siteParts['scheme'].'://'.$siteParts['host'];
    }
}