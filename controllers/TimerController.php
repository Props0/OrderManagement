<?php
include_once 'CommonController.php';
class TimerController extends CommonController
{
    public function __construct()
	{
        require '../vendor/autoload.php';
		parent::__construct();
	}
    public function get_closed_timers(){
        include_once '../models/Timer.php';
        $values = array();
        $values['getitems'] = "timer.*, project.name as projectname, tag.name as tagname";
        $values["object"] = new Timer();
        $values["unions"] = array(
            "table_extra"=>array(
                "table_name" => "project"
            ),
            "table_extra "=>array(
                "table_name" => "tag"
            ),	
            "table_extra  "=>array(
                "table_name" => "timertag"
            )		
        );
        $values["conditions"] = array(
			"timer.end "=>array(
				"signalvariable" => " IS NOT",
				"variable" => "NULL"
            ),
            "logicgate1"=>"and",
            "timer.projectid "=>array(
				"signalvariable" => "=",
				"variable" => "project.id"
            ),
            "logicgate2"=>"and",
            "timertag.timerid "=>array(
				"signalvariable" => "=",
				"variable" => "timer.id"
            ),
            "logicgate3"=>"and",
            "timertag.tagid "=>array(
				"signalvariable" => "=",
				"variable" => "tag.id"
            ),
		);
        $result = $this->get($values, "timer" );
        $timers = [];
        foreach ($result as $key => $row) {
            $start = new DateTime($row->getstart());
            $end   = new DateTime($row->getend());
            $interval = $start->diff($end);
            // Formata a duração como HH:MM:SS
            $elapsed  = $interval->format('%H:%I:%S');
            $row->elapsed=$elapsed;
        }
        echo json_encode(["success" => true, "timers" => $result]);
        exit;
    }
    public function start_timer($name, $project_name, $tag_name, $billable=0){
        require_once '../models/Project.php';
        include_once '../models/Tag.php';
        include_once '../models/Timer.php';
        include_once '../models/TimerTag.php';
        $createdby    = "user"; // Em um cenário real, substitua pelo usuário logado
        $start_time   = date("Y-m-d H:i:s");

        // Verificar ou criar projeto
        $projectid = null;
        $values = array();
        $values["object"] = new Project();
        $values["conditions"] = array(
			"name"=>array(
				"type" => "s",
				"signal" => "=",
				"val" => $project_name
			)
		);
        $result = $this->get($values,"project" );
        if (count($result) > 0) {
            $row       = $result[0];
            $projectid = $result[0]->getid();
        } else {
            $Project = new Project();
            $Project->setname($project_name);
            $Project->setcreatedby($createdby);
            $projectid = $this->insert($Project, "project");
        }

        // Verificar ou criar tag
        $tagid = null;
        if (!empty($tag_name)) {
            $values = array();
            $values["object"] = new Tag();
            $values["conditions"] = array(
                "name"=>array(
                    "type" => "s",
                    "signal" => "=",
                    "val" => $tag_name
                )
            );
            $result = $this->get($values, "tag" );
            if (count($result) > 0) {
                $row       = $result[0];
                $tagid = $result[0]->getid();
            } else {
                $Tag = new Tag();
                $Tag->setname($tag_name);
                $Tag->setcreatedby($createdby);
                $tagid = $this->insert($Tag, "tag");
            }
        }

        $timer = new timer();
        $timer->setname($name);
        $timer->setprojectid($projectid);
        $timer->setbillable($billable);
        $timer->setcreatedby($createdby);
        $timer->setstart($start_time);
        $timerid = $this->insert($timer, "timer");
        // Insere o timer com o horário de início
        if (!empty($tagid)) {
            $timertag = new timertag();
            $timertag->settimerid($timerid);
            $timertag->settagid($tagid);
            $timertag->setcreatedby($createdby);
            $timertagid = $this->insert($timertag, "timertag");
        }
        echo json_encode(["success" => true, "timerid" => $timerid]);
        exit;
    }

    public function end_timer($timer_id){
        include_once '../models/Timer.php';
        $end_time = date("Y-m-d H:i:s");

        $values= array(
            "end"=>array(
                "type" => "s",
                "signal" => "=",
                "val" => $end_time
            )
        );
        $conditions = array(
            "id"=>array(
                "type" => "i",
                "signal" => "=",
                "val" => $timer_id
            )
        );
        $this->update($values, $conditions, "timer");
      
        echo json_encode(["success" => true]);
        exit;
    }
    public function get_elapsed_time($timer_id){
        include_once '../models/Timer.php';
        $values = array();
        $values["object"] = new timer();
        $values["conditions"] = array(
			"id"=>array(
				"type" => "s",
				"signal" => "=",
				"val" => $timer_id
			)
		);
        $result = $this->get($values,"timer" );
        if (count($result) > 0) {
            $row   = $result;
            $start = new DateTime($row[0]->getstart());
            // Se o timer ainda estiver ativo, usa o horário atual
            $end = !empty($row[0]->getend()) ? new DateTime($row[0]->getend()) : new DateTime();
            $interval = $start->diff($end);
            // Formata o intervalo como HH:MM:SS
            $elapsed = $interval->format('%H:%I:%S');
            echo json_encode(["success" => true, "elapsed" => $elapsed]);
        } else {
            echo json_encode(["success" => false, "error" => "Timer não encontrado."]);
        }
        exit;
    }
    public function get_running_timers(){
        include_once '../models/Timer.php';
        $values = array();
        $values['getitems'] = "timer.*, project.name as projectname, tag.name as tagname";
        $values["object"] = new Timer();
        $values["unions"] = array(
            "table_extra"=>array(
                "table_name" => "project"
            ),
            "table_extra "=>array(
                "table_name" => "tag"
            ),	
            "table_extra  "=>array(
                "table_name" => "timertag"
            )		
        );
        $values["conditions"] = array(
			"timer.end "=>array(
				"signalvariable" => " IS ",
				"variable" => "NULL"
            ),
            "logicgate1"=>"and",
            "timer.projectid "=>array(
				"signalvariable" => "=",
				"variable" => "project.id"
            ),
            "logicgate2"=>"and",
            "timertag.timerid "=>array(
				"signalvariable" => "=",
				"variable" => "timer.id"
            ),
            "logicgate3"=>"and",
            "timertag.tagid "=>array(
				"signalvariable" => "=",
				"variable" => "tag.id"
            ),
		);
        $result = $this->get($values, "timer" );
        $timers = [];
        foreach ($result as $key => $row) {
            $start = new DateTime($row->getstart());
            $end   = new DateTime($row->getend());
            $interval = $start->diff($end);
            // Formata a duração como HH:MM:SS
            $elapsed  = $interval->format('%H:%I:%S');
            $row->elapsed=$elapsed;
        }
        echo json_encode(["success" => true, "timers" => $result]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] != '') {
    $controller = new TimerController();
    $params=(isset($_POST) && $_POST!=null) ? $_POST : ((isset($_GET) && $_GET!=null) ? $_GET : []);
    $uri = explode("/",$_SERVER['REQUEST_URI']);
    $action_name =  end($uri);
    call_user_func_array(array($controller, $action_name), $params);
}

?>