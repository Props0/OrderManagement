<?php
 class TimerTag{
    public $id ;
    public $timerid ;
    public $tagid ;
    public $createdby ;

    function getid(){
        return $this->id;

    }

    function setid($_id){
        $this->id = $_id;
    }

    function gettimerid(){
        return $this->timerid;

    }

    function settimerid($_timerid){
        $this->timerid = $_timerid;
    }
    function gettagid(){
        return $this->tagid;

    }

    function settagid($_tagid){
        $this->tagid = $_tagid;
    }

    function getcreatedby(){
        return $this->createdby;

    }

    function setcreatedby($_createdby){
        $this->createdby = $_createdby;
    }

 }

