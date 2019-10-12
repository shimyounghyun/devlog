<?php
namespace application\controller;

Class Controller {
    //변수
//    var $param;
//    var $db;
//    var $title;
//    var $setAjax;
//
//    //생성자
//    function __construct($param){
//        header("Content-type:text/html;charset=utf8");
//        $this->param = $param;
//        $modelName = "Model_{$this->param->page_type}";
//        $this->db = new $modelName($this->param);
//        $this->setAjax = false;
//        $this->index();
//    }
//    //index
//    function index(){
//        $method = isset($this->param->action) ? $this->param->action : 'basic';
//        if(method_exists($this,$method)) $this->$method();
//        $this->getTitle();
//        $this->header();
//        $this->content();
//        $this->footer();
//    }
//    //header
//    function header(){
//        $this->setAjax || require_once(_VIEW."header.php");
//    }
//    //footer
//    function footer(){
//        $this->setAjax || require_once(_VIEW."footer.php");
//    }
//    //content
//    function content(){
//        $this_arr = (array)$this;
//        extract($this_arr);
//        $dir = _VIEW."{$this->param->page_type}/{$this->param->include_file}.php";
//        if(file_exists($dir)) require_once($dir);
//    }
//
//    //getTitle
//    function getTitle(){
//        $this->title = 'Devlog';
//    }

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $route_params = [];
    /**
     * Class constructor
     *
     * @param array $route_params  Parameters from the route
     *
     * @return void
     */
    public function __construct($route_params)
    {
        $this->route_params = $route_params;
    }
    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param string $name  Method name
     * @param array $args Arguments passed to the method
     *
     * @return void
     */
    public function __call($name, $args)
    {
        $method = $name . 'Action';
        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }
    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
    }
    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }
}
?>