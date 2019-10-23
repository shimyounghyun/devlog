<?php
    namespace App\Models;
    use Core\Model;
    use PDO;

    class Comment extends Model{

        /**
         * 댓글을 등록한다.
         * @param $form
         */
        function insertComment($form){
            $this->sql = "
                INSERT INTO COMMENT(
                    COMMENT_CONTENT
                    ,USER_ID
                    ,CREATE_DATE
                    ,COMMENT_DELETE
                    ,POST_SEQ
                    ,COMMENT_PARRENT                    
                )VALUES(
                    ?
                    ,?
                    ,NOW()
                    ,false
                    ,?
                    ,?
                )
            ";

            $this->column = [
                $form['comment_content']
                ,$_SESSION["USER"]->USER_ID
                ,(int)$form['post_seq']
                ,(int)$form['comment_parent']
            ];

            $this->query();
        }

        /**
         * 댓글의 총 개수를 반환한다.
         * @param $post_seq
         */
        function selectCommentCount($post_seq){
            $this->sql = "
                            SELECT
                                COUNT(*) AS `COUNT`
                            FROM
                                COMMENT
                            WHERE
                                POST_SEQ = :post_seq 
                                AND COMMENT_DELETE = FALSE";

            $this->column = [
                ':post_seq'=>(int)$post_seq
            ];

            return $this->fetch()->COUNT;
        }

        /**
         * 게시글 번호로 댓글 목록을 가져온다.
         * @param $post_seq
         */
        function selectCommentList($post_seq,$page_num){
            if(!isset($page_num)) $page_num = 1;
            $page_num = ($page_num - 1) * 10;

            $this->sql = "
            SELECT
                    COMMENT.COMMENT_CONTENT
                    ,DATE_FORMAT(COMMENT.CREATE_DATE, '%Y-%m-%d') CREATE_DATE
                    ,COMMENT.USER_ID
                    ,COMMENT.POST_SEQ
                    ,COMMENT.COMMENT_SEQ
                    ,COMMENT.DEPTH
                    ,U.USER_THUMBNAIL
            FROM(
                    SELECT
                        C.COMMENT_CONTENT
                        ,C.CREATE_DATE
                        ,C.USER_ID
                        ,C.POST_SEQ
                        ,C.COMMENT_SEQ
                        ,SORT.DEPTH
                    FROM
                    (
                        SELECT 
                                hierarchy(COMMENT_SEQ)
                                ,@level AS DEPTH
                                ,COMMENT_CONTENT
                                ,COMMENT_SEQ
                        FROM
                            (
                                SELECT @start_with := 0 ,
                                       @idx := @start_with,
                                       @level := 0 
                            ) VARS, COMMENT
                        WHERE @idx IS NOT NULL
                              AND POST_SEQ = :post_seq
                              AND COMMENT_DELETE = FALSE
                    ) SORT
                    INNER JOIN COMMENT C ON SORT.COMMENT_SEQ = C.COMMENT_SEQ
                ) COMMENT
                INNER JOIN USER U ON COMMENT.USER_ID = U.USER_ID
            LIMIT :page_num , 10;
            ";

            $res = $this->db->prepare($this->sql);
            $res->bindValue(':post_seq',$post_seq,PDO::PARAM_INT);
            $res->bindValue(':page_num',$page_num, PDO::PARAM_INT);
            $res->execute();
            return $res->fetchAll();
        }
    }