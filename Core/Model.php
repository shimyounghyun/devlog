<?php
namespace Core;
use PDO;
//use App\Config;
/**
 * Base model
 *
 * PHP version 7.0
 */
class Model
{
    //변수
    var $db;
    var $column;
    var $table;
    var $action;
    var $sql;
    var $bind;

    //생성자
    function __construct(){
        $dbConfig = parse_ini_file("db_config.ini");
        $this->column = NULL;
        $this->db = new PDO(
            "mysql:host=127.0.0.1;dbname=".$dbConfig["db"].";"."charset=utf8"
            ,$dbConfig["username"]
            ,$dbConfig["password"]);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }
    // query
    function query($sql = false){
        $sql && $this->sql = $sql;
        $res = $this->db->prepare($this->sql);
        if($res->execute($this->column)){
            return $res;
        } else {
//            echo "<pre>";
//            echo $this->sql;
//            print_r($this->column);
//            print_r($this->db->errorInfo());
//            echo "</pre>";
            return $this->db->errorInfo();
        }
    }
    //fetch
    function fetch($sql = false){
        $sql && $this->sql = $sql;
        return $this->query($this->sql)->fetch();
    }
    //fetchAll
    function fetchAll($sql = false){
        $sql && $this->sql = $sql;
        return $this->query($this->sql)->fetchAll();
    }
    //cnt
    function cnt($sql = false){
        $sql && $this->sql = $sql;
        return $this->query($this->sql)->rowCount();
    }
    //column
    function getColumn($arr,$cancel){
        $column = '';
        $cancel = explode("/",$cancel);
        foreach ($arr as $key => $value) {
            if(!in_array($key,$cancel)){
                $column .= ", {$key} = :{$key}\n";
                $this->column[$key] = $value;
            }
        }
        return $column = substr($column,2);
    }
    //queryResult
    function combine($column){
        switch($this->action){
            case 'insert' : $sql = " INSERT INTO {$this->table} set \n"; break;
            case 'update' : $sql = " UPDATE {$this->table} set \n"; break;
            case 'delete' : $sql = " DELETE FROM {$this->table} \n"; break;
        }
        return $sql .= $column;
    }
}