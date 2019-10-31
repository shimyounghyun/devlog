<?php


namespace App\Models;
use Core\Model;
use PDO;


class News extends Model{

    /**
     * 뉴스 총 개수 반환
     * @return mixed
     */
    function selectNewsTotalCount(){
        $this->sql="
            SELECT
                COUNT(*) COUNT
            FROM
                NEWS            
        ";

        return $this->fetch()->COUNT;
    }

    /**
     * 뉴스 목록 가져오기
     */
    function selectNewsList($page_num){
        $page_num = $page_num * 12;
        $this->sql="
            SELECT
               TRANS_TITLE
               ,ORIGIN_TITLE
               ,ORIGIN_SUB_TITLE
               ,NEWS_WRITER
               ,NEWS_DATE
               ,NEWS_LINK
               ,NEWS_SEQ
               ,NEWS_THUMBNAIL
            FROM
                NEWS
            ORDER BY
                NEWS_SEQ DESC
                LIMIT :page_num, 12;
        ";

        $res = $this->db->prepare($this->sql);
        $res->bindValue(':page_num',$page_num,PDO::PARAM_INT);
        $res->execute();

        return $res->fetchAll();
    }

    /**
     * 뉴스 제목 수정
     * @param $form
     */
    function updateNews($form){
        $this->sql = "
            UPDATE
                NEWS
            SET
                TRANS_TITLE = :trans_title
            WHERE
                NEWS_SEQ = :news_seq
        ";

        $this->column = [
            ':trans_title'=>$form['trans_title']
            ,':news_seq'=>$form['news_seq']
        ];

        $this->query();
    }

    /**
     * 뉴스를 등록한다.
     */
    function insertNews($form){

        $this->sql = "
            INSERT INTO NEWS( 
                TRANS_TITLE
                ,ORIGIN_TITLE
                ,ORIGIN_SUB_TITLE
                ,NEWS_WRITER
                ,NEWS_DATE
                ,NEWS_LINK
                ,NEWS_THUMBNAIL
            )VALUES(
                :trans_title
                ,:origin_title
                ,:origin_sub_title
                ,:news_writer
                ,:news_date
                ,:news_link
                ,:news_thumb
            )
        ";

        $this->column = [
            ':trans_title'=>$form['trans_title']
            ,':origin_title'=>$form['origin_title']
            ,':origin_sub_title'=>$form['origin_sub_title']
            ,':news_writer'=>$form['news_writer']
            ,':news_date'=>$form['news_date']
            ,':news_link'=>$form['news_link']
            ,':news_thumb'=>$form['news_thumb']
        ];

        return $this->query();
    }

    function hasNews($form){
        $this->sql = "
            SELECT
                COUNT(*) COUNT
            FROM
                NEWS
            WHERE
                ORIGIN_TITLE = :origin_title
        ";

        $this->column = [
            ':origin_title'=>$form['origin_title']
        ];

        return $this->fetch()->COUNT > 0 ? true : false;
    }

    /**
     * 번역이 안된 제목 기사 목록 모두 가져오기
     */
    function selectNonTransTitleNewsList(){
        $this->sql = "
            SELECT
                ORIGIN_TITLE
                ,NEWS_SEQ
            FROM
                NEWS
            WHERE
                TRANS_TITLE IS NULL                
        ";

        return $this->fetchAll();
    }
}