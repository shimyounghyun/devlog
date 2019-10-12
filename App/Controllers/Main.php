<?php
namespace App\Controllers;

use Core\View;

class Main extends \Core\Controller {

    function basicAction(){
        View::render("header.html");
        View::render("main/main.html");
    }

    function policyAction(){
        View::render("header.html");
        View::render("footer.html");
    }

    function toastAction(){
        echo "안녕하세요오";
    }
}
?>