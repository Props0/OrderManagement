<?php
include_once 'CommonController.php';
class BlogController extends CommonController
{
	public function __construct()
	{
		parent::__construct();
		$this->table = "blog";
	}
    function isauth(){
        session_start();
        return  isset($_SESSION["isauth"]) && $_SESSION["isauth"];
    }
    function tryauth($password){
        session_start();
        $_SESSION["isauth"] = $password=="Mocsa_2024";
        return  $_SESSION["isauth"];
    }
    function getbloglist($limit = null){
        
        include_once 'models/blog.php';
        $values = array();
        $values["object"] = new blog();
        $values["orderby"] = " date desc";
        $result = $this->get($values, null, $limit ? " LIMIT ".$limit : "" );
        
        return $result;
    }
    function getblog($id){
        $values = array();
        require_once '../models/blog.php';
        $values["object"] = new blog();
        $values["conditions"] = array(
			"id"=>array(
				"type" => "i",
				"signal" => "=",
				"val" => $id
			)
		);
        $result = $this->get($values);
        
        echo json_encode($result[0]);
    }
    function deleteblog($id){
        if($this->isauth()){
            $values = array();
            require_once '../models/blog.php';
            $conditions = array(
                "id"=>array(
                    "type" => "i",
                    "signal" => "=",
                    "val" => $id
                )
            );
            $result = $this->delete($conditions);
        }
    }
    function save($title, $text, $image){
        if($this->isauth()){
            require_once '../models/blog.php';
            $blog = new Blog();
            $blog->settitle($title);
            $blog->settext($text);
            $blog->setimage($image);
            $this->insert($blog);
        }
    }
    function updateblog($id, $title, $text, $image){
        if($this->isauth()){
            require_once '../models/blog.php';
            $values= array(
                "title"=>array(
                    "type" => "s",
                    "signal" => "=",
                    "val" => $title
                ),
                "text"=>array(
                    "type" => "s",
                    "signal" => "=",
                    "val" => $text
                ),
                "image"=>array(
                    "type" => "s",
                    "signal" => "=",
                    "val" => $image
                )
            );
            $conditions = array(
                "id"=>array(
                    "type" => "i",
                    "signal" => "=",
                    "val" => $id
                )
            );
            $this->update($values, $conditions);
        }
    }
    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] != '') {
    $controller = new BlogController();
    $params=(isset($_POST) && $_POST!=null) ? $_POST : ((isset($_GET) && $_GET!=null) ? $_GET : []);
    $uri = explode("/",$_SERVER['REQUEST_URI']);
    $action_name =  end($uri);
    call_user_func_array(array($controller, $action_name), $params);
}