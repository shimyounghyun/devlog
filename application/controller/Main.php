<?php
namespace application\controller;
class Main extends Controller {

    function basicAction(){
        require_once(_VIEW."main/main.php");
        require_once(_VIEW."footer.php");
        require_once(_VIEW."header.php");
    }

}
?>