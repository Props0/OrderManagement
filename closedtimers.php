<?php 
include_once 'controllers/CommonController.php';
include_once 'models/Timer.php';
$timers = $_POST['timers']?>

<div class="card p-4 shadow-sm border-0">
    <div id="closedTimersContainer" class="list-group">
        <!-- Os timers serão inseridos aqui -->
        <ul class="list-unstyled">
            <?php foreach ($timers as $key => $timerdata) { 
                $controller = new CommonController();
                $timer = $controller->buildObject($timerdata, new timer());
                ?>
            <li class="mb-3 p-3 rounded bg-light border">
                <div class="timer-info">
                    <h5 class="mb-2"><?php echo $timer->getname(); ?></h5>
                    <p class="mb-1 text-muted">
                        <strong>Iniciado em:</strong> <?php echo $timer->getstart(); ?> 
                        <span class="mx-2">|</span>
                        <strong>Terminado em:</strong> <?php echo $timer->getend(); ?> 
                    </p>
                    <p class="mb-1 text-muted">
                        <strong>Duração:</strong> <?php echo $timer->getelapsed(); ?> 
                        <span class="mx-2">|</span>
                        <strong>Tag:</strong> <?php echo $timer->gettagname(); ?>
                    </p>
                    <p class="mb-0 text-muted">
                        <strong>Projeto:</strong> <?php echo $timer->getprojectname(); ?>
                    </p>
                </div>
            </li>
            <?php }?>
        </ul>
    </div>
</div>