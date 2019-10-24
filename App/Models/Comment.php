<?php
    namespace App\Models;
    use Core\Model;
    use PDO;

    class Comment extends Model{

        function updateComment($form){
            $this->sql = "
                UPDATE
                    COMMENT
                SET
                    COMMENT_CONTENT = :comment_content
                WHERE
                    COMMENT_SEQ = :comment_seq
            ";
            $this->column = [
                ':comment_content'=>$form['comment_content']
                ,':comment_seq'=>$form['comment_origin']
            ];

            $this->query();
        }

        /**
         * 댓글을 등록한다.
         * @param $form
         */
        function insertComment($form){
            $comment_group = ':comment_group';
            $comment_order = "(SELECT COMMENT_ORDER + 1 FROM COMMENT C WHERE COMMENT_SEQ = ".(int)$form['comment_origin'].")";
            $comment_level = "(SELECT COMMENT_LEVEL + 1 FROM COMMENT C WHERE COMMENT_SEQ = ".(int)$form['comment_origin'].")";

            $this->column = [
                ':comment_content'=>$form['comment_content']
                ,':user_id'=>$_SESSION["USER"]->USER_ID
                ,':post_seq'=>(int)$form['post_seq']
                ,':comment_group'=>(int)$form['comment_group']
            ];

            if($form['comment_group'] == '0'){
                $comment_group = "(SELECT 
                            AUTO_INCREMENT
                        FROM 
                            INFORMATION_SCHEMA.TABLES
                        WHERE 
                            TABLE_NAME = 'COMMENT'
                            AND TABLE_SCHEMA = DATABASE()
                    )";

                $comment_order = 0;
                $comment_level = 0;

                $this->column = [
                    ':comment_content'=>$form['comment_content']
                    ,':user_id'=>$_SESSION["USER"]->USER_ID
                    ,':post_seq'=>(int)$form['post_seq']
                ];
            }else{
                $this->updateCommentOrder($form);
            }

            $this->sql = "
                INSERT INTO COMMENT(
                    COMMENT_CONTENT
                    ,USER_ID
                    ,CREATE_DATE
                    ,COMMENT_DELETE
                    ,POST_SEQ
                    ,COMMENT_GROUP
                    ,COMMENT_ORDER
                    ,COMMENT_LEVEL                 
                )VALUES(
                    :comment_content
                    ,:user_id
                    ,NOW()
                    ,false
                    ,:post_seq
                    ,".$comment_group."
                    ,".$comment_order."
                    ,".$comment_level."
                )
            ";

            $this->query();

        }

        function deleteComment($num){
            $this->sql = "
                UPDATE
                    COMMENT
                SET
                    COMMENT_DELETE = TRUE
                WHERE
                    COMMENT_SEQ = :num
            ";

            $this->column = [
                ':num'=>(int)$num
            ];

            $this->query();
        }

        function updateCommentOrder($form){
            $this->sql = "
                UPDATE 
                    COMMENT 
                SET 
                    COMMENT_ORDER = COMMENT_ORDER+1 
                WHERE 
                    COMMENT_GROUP = :comment_group
                    AND COMMENT_ORDER > (SELECT COMMENT_ORDER FROM COMMENT C WHERE COMMENT_SEQ = :comment_origin)";

            $res = $this->db->prepare($this->sql);
            $res->bindValue(':comment_origin',(int)$form['comment_origin'],PDO::PARAM_INT);
            $res->bindValue(':comment_group',(int)$form[':comment_group'],PDO::PARAM_INT);
            $res->execute();
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
                C.COMMENT_CONTENT
                ,DATE_FORMAT(C.CREATE_DATE, '%Y-%m-%d') CREATE_DATE
                ,C.USER_ID
                ,C.COMMENT_SEQ
                ,C.POST_SEQ
                ,C.COMMENT_LEVEL
                ,C.COMMENT_ORDER
                ,C.COMMENT_GROUP
                ,C.COMMENT_DELETE
                ,U.USER_THUMBNAIL
            FROM
                COMMENT C
                INNER JOIN USER U ON C.USER_ID = U.USER_ID
            WHERE
                POST_SEQ = :post_seq
            ORDER BY
                COMMENT_GROUP DESC, COMMENT_ORDER ASC
            LIMIT :page_num , 10;
            ";

            $res = $this->db->prepare($this->sql);
            $res->bindValue(':post_seq',$post_seq,PDO::PARAM_INT);
            $res->bindValue(':page_num',$page_num, PDO::PARAM_INT);
            $res->execute();
            return $res->fetchAll();
        }
    }