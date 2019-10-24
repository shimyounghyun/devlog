<?php
    namespace App\Models;
    use Core\Model;

    class Post extends Model{

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
                    ,TAG
                    ,DATE_FORMAT(CREATE_DATE, '%Y년 %m월 %d일') AS CREATE_DATE
                    ,POST_SEQ
                FROM
                    POST
                WHERE
                    POST_DELETE = FALSE
                    AND POST_SEQ = '{$id}'
                    AND POST_TYPE = '{$post_type}'
            ";
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