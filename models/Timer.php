<?php
 class Timer{
    public $id ;
    public $name ;
    public $start ;
    public $end ;
    public $billable ;
    public $projectid ;
    public $projectname ;
    public $tagname ;
    public $elapsed ;
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

    function getend(){
        return $this->end;

    }

    function setend($_end){
        $this->end = $_end;
    }

    function getstart(){
        return $this->start;

    }

    function setstart($_start){
        $this->start = $_start;
    }

    function getbillable(){
        return $this->billable;

    }

    function setbillable($_billable){
        $this->billable = $_billable;
    }
    function getprojectid(){
        return $this->projectid;

    }

    function setprojectid($_projectid){
        $this->projectid = $_projectid;
    }

    function getcreatedby(){
        return $this->createdby;

    }

    function setcreatedby($_createdby){
        $this->createdby = $_createdby;
    }

    function getprojectname(){
        return $this->projectname;

    }

    function setprojectname($_projectname){
        $this->projectname = $_projectname;
    }

    function gettagname(){
        return $this->tagname;

    }

    function settagname($_tagname){
        $this->tagname = $_tagname;
    }

    function getelapsed(){
        return $this->elapsed;

    }

    function setelapsed($_elapsed){
        $this->elapsed = $_elapsed;
    }

 }

