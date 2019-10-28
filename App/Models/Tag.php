<?php
namespace App\Models;
use Core\Model;
use PDO;

class Tag extends Model{

    /**
     * 태그를 등록한다.
     * @param $tag_name
     * @param $post_seq
     */
    function insertTag($tag_name, $post_seq){
        $this->sql="
            INSERT INTO TAG(
                TAG_NAME
                ,POST_SEQ
            )VALUES(
                :tag_name
                ,:post_seq
            )
        ";

        $this->column = [
            ':tag_name'=>$tag_name
            ,':post_seq'=>$post_seq
        ];

        $this->query();
    }

    /**
     * 등록된 태그를 삭제한다.
     * @param $post_seq
     */
    function deleteTag($post_seq){
        $this->sql="
            DELETE FROM TAG
            WHERE
                POST_SEQ = :post_seq
        ";

        $this->column = [
            ':post_seq'=>$post_seq
        ];

        $this->query();
    }

    /**
     * 전체 태그 목록을 가져온다.
     */
    function selectTagList($sort){
        $orderby = "";
        if($sort == "name"){
            $orderby = " ORDER BY T.TAG_NAME ASC";
        }else{
            $orderby = " ORDER BY COUNT DESC";
        }

        $this->sql="
            SELECT
                TAG_NAME
                ,COUNT(TAG_NAME) COUNT
            FROM
                TAG T
                INNER JOIN POST P ON T.POST_SEQ = P.POST_SEQ
            WHERE
                P.POST_DELETE = FALSE
            GROUP BY
                TAG_NAME".$orderby;

        return $this->fetchAll();
    }

    /**
     * 태그명이 등록된 게시물 전체 개수 반환
     * @param $tag_name
     * @return mixed
     */
    function selectPostByTagTotalCount($tag_name){
        $this->sql = "
            SELECT
                COUNT(*) COUNT
            FROM
                TAG T
                INNER JOIN POST P ON T.POST_SEQ = P.POST_SEQ
            WHERE
                P.POST_DELETE = FALSE
            	AND T.TAG_NAME = :tag_name;
        ";

        $this->column = [
            ':tag_name'=>$tag_name
        ];

        return $this->fetch()->COUNT;
    }

    /**
     * 태그가 등록된 게시물 목록 가져오기
     */
    function selectPostListByTag($tag_name,$page_num){
        $page_num = $page_num * 12;
        $this->sql="
            SELECT
                P.POST_SEQ
                ,P.POST_TITLE
                ,P.POST_CONTENT
                ,P.POST_THUMBNAIL
                ,P.USER_ID
                ,P.ALL_VISIBLE
                ,P.SERIES_ID
                ,DATE_FORMAT(P.CREATE_DATE, '%Y년 %m월 %d일') AS CREATE_DATE
                ,(SELECT COUNT(*) FROM COMMENT WHERE POST_SEQ = P.POST_SEQ) AS COMMENT_COUNT
            FROM
                TAG T
                INNER JOIN POST P ON T.POST_SEQ = P.POST_SEQ
            WHERE
                P.POST_DELETE = FALSE
                AND P.ALL_VISIBLE  = TRUE
            	AND T.TAG_NAME = :tag_name
            ORDER BY
                P.POST_SEQ DESC
                LIMIT :page_num, 12;
        ";

//        return $this->sql;
        $res = $this->db->prepare($this->sql);
        $res->bindValue(':tag_name',$tag_name,PDO::PARAM_STR);
        $res->bindValue(':page_num',$page_num,PDO::PARAM_INT);
        $res->execute();

        return $res->fetchAll();
    }
}