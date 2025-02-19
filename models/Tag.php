<?php
 class Tag{
    public $id ;
    public $name ;
    public $createdby ;

    function getid(){
        return $this->id;

    }

    function setid($_id){
        $this->id = $_id;
    }

    function getname(){
        return $this->name;

    }

    function setname($_name){
        $this->name = $_name;
    }

    function getcreatedby(){
        return $this->createdby;

    }

    function setcreatedby($_createdby){
        $this->createdby = $_createdby;
    }

 }

