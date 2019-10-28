<?php
    namespace App\Models;
    use Core\Model;
    use PDO;

    class Post extends Model{

        /**
         * 검색된 목록 개수 반환
         * @param $text
         * @return mixed
         */
        function selectSearchPostCount($text){
            $text = '%'.$text.'%';
            $this->sql = "
                SELECT
                    COUNT(*) COUNT
                FROM
                    POST P
                    INNER JOIN USER U ON P.USER_ID = U.USER_ID
                WHERE
                    POST_DELETE = FALSE
                    AND POST_CONTENT LIKE :text
            ";

            $this->column = [
                ':text'=>$text
            ];

            return $this->fetch()->COUNT;
        }

        /**
         * 검색어로 게시글 목록 가져오기
         * @param $text
         * @param $page_num
         * @return array
         */
        function selectSearchPostList($text, $page_num){
            $page_num = $page_num * 10;
            $text = '%'.$text.'%';

            $this->sql = "
                SELECT
                    P.POST_CONTENT
                    ,P.POST_TITLE
                    ,P.USER_ID
                    ,DATE_FORMAT(P.CREATE_DATE,'%Y-%m-%d') CREATE_DATE
                    ,U.USER_THUMBNAIL
                    ,P.POST_SEQ
                FROM
                    POST P
                    INNER JOIN USER U ON P.USER_ID = U.USER_ID
                WHERE
                    POST_DELETE = FALSE
                    AND POST_CONTENT LIKE :text
                LIMIT :page_num, 10
                    
            ";

            $res = $this->db->prepare($this->sql);
            $res->bindValue(':text',$text,PDO::PARAM_STR);
            $res->bindValue(':page_num',$page_num,PDO::PARAM_INT);
            $res->execute();

            return $res->fetchAll();
        }

        /**
         * 작성자의 다른 게시물 목록 가져오기
         * @param $post_seq
         * @return array
         */
        function selectOtherPost($post_seq){
            $this->sql = "
            SELECT
             R.*
            FROM(
                SELECT
                    ROW_NUMBER() OVER() ROWNUM
                    ,(SELECT IF(COUNT(*) > 4, 4, COUNT(*)) FROM POST WHERE POST_SEQ < :post_seq AND POST_DELETE = FALSE ) PREV_COUNT
                    ,(SELECT IF(COUNT(*) > 4, 4, COUNT(*)) FROM POST WHERE POST_SEQ > :post_seq AND POST_DELETE = FALSE ) NEXT_COUNT
                    ,P.POST_CONTENT
                    ,P.POST_TITLE
                    ,P.POST_SEQ
                    ,DATE_FORMAT(P.CREATE_DATE, '%Y-%m-%d') CREATE_DATE
                FROM
                    POST P
                WHERE
                    POST_SEQ >= (SELECT
                                    IFNULL(MIN(P.POST_SEQ),:post_seq)
                                FROM(
                                    SELECT
                                        POST_SEQ
                                    FROM
                                        POST
                                    WHERE
                                        POST_SEQ < :post_seq
                                        AND POST_DELETE = FALSE
                                    ORDER BY
                                        POST_SEQ DESC
                                    LIMIT 4
                                ) P
                        )
                    AND
                    POST_SEQ <= (SELECT
                                    IFNULL(MAX(P.POST_SEQ), :post_seq)
                                FROM(
                                    SELECT
                                        POST_SEQ
                                    FROM
                                        POST
                                    WHERE
                                        POST_SEQ > :post_seq
                                        AND POST_DELETE = FALSE
                                    LIMIT 4
                                ) P
                        )
                    
                ) R
            WHERE
                (
                    ( PREV_COUNT < 2 AND  ROWNUM <= 5)
                    OR
                    (NEXT_COUNT < 2 AND ROWNUM <= (PREV_COUNT + NEXT_COUNT + 1) AND  (PREV_COUNT + NEXT_COUNT - 3) <= ROWNUM)
                    OR
                    (NEXT_COUNT >=2 AND PREV_COUNT >=2 AND PREV_COUNT - 1 <= ROWNUM AND PREV_COUNT + 3  >= ROWNUM)
                    
                )
            ORDER BY POST_SEQ DESC
            ";

            $this->column = [
              ':post_seq'=>(int)$post_seq
            ];

            return $this->fetchAll();
        }

        /**
         * 게시물 총 개수를 반환한다.
         */
        function selectPostTotalCount($post_type){
            $this->sql = "
                SELECT
                    COUNT(*) COUNT
                FROM
                    POST
                WHERE
                    POST_DELETE = FALSE
                    AND POST_TYPE = '{$post_type}'
            ";

            return $this->query()->fetch()->COUNT;
        }

        function deletePost($post_seq){
            $this->sql = "
                UPDATE
                    POST
                SET
                    POST_DELETE = TRUE
                WHERE
                    POST_SEQ = :post_seq
            ";

            $this->column = [
              ':post_seq'=>$post_seq
            ];

            $this->query();
        }

        /**
         * 새글을 등록한다.
         * @param $form
         */
        function insertPost($form){
            $this->sql =
                "INSERT INTO POST(
                            POST_TITLE
                            ,POST_CONTENT
                            ,POST_THUMBNAIL
                            ,USER_ID
                            ,ALL_VISIBLE
                            ,SERIES_ID
                            ,TAG
                            ,CREATE_DATE
                        )VALUES (
                            ?
                            ,?
                            ,?
                            ,?
                            ,?
                            ,?
                            ,?
                            ,NOW()
                        )";

            $this->column = [
                (string)$form['post_title']
                ,$form['post_content']
                ,$form['post_thumbnail']
                ,$_SESSION["USER"]->USER_ID
                ,(bool)$form['post_visible']
                ,(string)$form['series_id']
                ,(string)$form['tag']
            ];
            $this->query();
            return $this->db->lastInsertId();
        }

        function updatePost($form){
            $this->sql = "
                UPDATE
                    POST
                SET
                    POST_TITLE = IFNULL(:post_title, POST_TITLE)
                    ,POST_CONTENT = IFNULL(:post_content, POST_CONTENT)
                    ,POST_THUMBNAIL = IFNULL(:post_thumbnail, POST_THUMBNAIL)
                WHERE
                    POST_SEQ = :post_seq                    
            ";

            $this->column = [
                ':post_title'=>$form['post_title']
                ,':post_content'=>$form['post_content']
                ,':post_thumbnail'=>$form['post_thumbnail']
                ,':post_seq'=>$form['post_seq']
            ];

            $this->query();
        }

        /**
         * 글 하나를 가져온다.
         * @param $id
         */
        function selectPost($id, $post_type){
            $this->sql = "
                SELECT
                    POST_TITLE
                    ,POST_CONTENT
                    ,POST_THUMBNAIL
                    ,USER_ID
                    ,ALL_VISIBLE
                    ,SERIES_ID
                    ,DATE_FORMAT(CREATE_DATE, '%Y년 %m월 %d일') AS CREATE_DATE
                    ,P.POST_SEQ
                    ,GROUP_CONCAT(T.TAG_NAME) TAG
                FROM
                    POST P
                    LEFT OUTER JOIN TAG T ON P.POST_SEQ = T.POST_SEQ 
                WHERE
                    POST_DELETE = FALSE
                    AND P.POST_SEQ = :post_seq
                    AND P.POST_TYPE = :post_type
                    AND T.POST_SEQ = :post_seq
            ";

            $this->column = [
                ':post_seq'=>$id
                ,':post_type'=>$post_type
            ];
            return $this->fetch();
        }

        /**
         * 글 목록을 최신순으로 가져온다.
         */
        function selectPortfolioList($index){
            $index = $index * 12;
            $this->sql =
                            "SELECT
                                POST_SEQ
                                ,POST_TITLE
                                ,POST_CONTENT
                                ,POST_THUMBNAIL
                                ,DATE_FORMAT(P.CREATE_DATE,'%Y-%m-%d') CREATE_DATE
                                ,UPDATE_DATE
                                ,P.USER_ID
                                ,U.USER_THUMBNAIL
                                ,TAG
                                ,SERIES_ID
                                ,(SELECT COUNT(*) FROM COMMENT WHERE POST_SEQ = P.POST_SEQ) AS COMMENT_COUNT
                            FROM
                                POST P
                                INNER JOIN USER U ON P.USER_ID = U.USER_ID
                            WHERE
                                POST_DELETE = FALSE
                                AND POST_TYPE = 0
                                AND ALL_VISIBLE = TRUE
                            ORDER BY 
                                POST_SEQ DESC
                                LIMIT {$index}, 12;
            ";

            return $this->fetchAll();
        }
    }
?>