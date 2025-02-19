<?php 
include_once 'controllers/CommonController.php';
include_once 'models/Timer.php';
$timers = $_POST['timers']?>

<div class="card p-4 shadow-sm border-0">
    <div id="closedTimersContainer" class="list-group">
        <ul class="list-unstyled">
            <?php foreach ($timers as $key => $timerdata) { 
                $controller = new CommonController();
                $timer = $controller->buildObject($timerdata, new timer());
                ?>
            <li id="timer-<?php echo $timer->getid(); ?>" class="d-flex justify-content-between align-items-center py-3 mb-3 border-bottom">
                <div class="timer-info d-flex align-items-center w-100">
                    <!-- Roda de cronômetro -->
                    <div class="timer-circle-container me-3 position-relative">
                        <svg class="timer-circle" viewBox="0 0 36 36">
                            <circle class="circle-bg" cx="18" cy="18" r="16" />
                            <circle class="circle-progress" cx="18" cy="18" r="16" />
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <strong><?php echo $timer->getname(); ?></strong>
                        <div class="text-muted">Iniciado em: <?php echo $timer->getstart(); ?></div>
                        <div class="ms-2">Tempo decorrido: <span class="elapsed-time" data-start-time="<?php echo strtotime($timer->getstart()); ?>"></span></div>
                    </div>
                </div>
                <div class="timer-action">
                <div class="timer-action">
                    <button class="btn btn-danger btn-sm" onclick="stopTimerById(<?php echo $timer->getid(); ?>);">Parar</button>
                </div>
                </div>
            </li>
            <?php } ?>
        </ul>
    </div>
</div>




