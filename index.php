<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Timers - Contagem no Servidor</title>
    <!-- Inclua o CSS do Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Inclua o Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Inclua o jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link href="assets/css/style.css" rel="stylesheet">
    <script>
        let timerId = null;       // ID do timer iniciado pelo formulário (se houver)
        let timerInterval = null; // Intervalo para atualizar o tempo do timer iniciado
        let listInterval  = null; // Intervalo para atualizar a lista de timers em execução
        let htmlexecution = "";
        let htmlpasted = "";
        // Função para carregar e exibir os timers fechados
        function loadClosedTimers() {
            const formData = new FormData();
            fetch('controllers/TimerController.php/get_closed_timers', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const formData = new FormData();
                    if (data.timers.length > 0) {
                        data.timers.forEach((item, index) => {
                            for (const key in item) {
                            formData.append(`timers[${index}][${key}]`, item[key]);
                            }
                        });
                        fetch('closedTimers.php', { method: 'POST', body: formData })
                            .then(response => response.text())
                            .then(htmlpasted => {
                                document.getElementById('closedTimersList').innerHTML = htmlpasted;
                            })
                            .catch(error => console.error('Erro ao carregar o HTML:', error));
                    } else {
                        htmlpasted = 'Nenhum timer fechado.';
                    }
                    document.getElementById('closedTimersList').innerHTML = htmlpasted;
                } else {
                    console.error(data.error);
                }
            });
        }


        // Atualiza o tempo decorrido do timer iniciado pelo formulário
        function updateElapsedTime() {
            if (!timerId) return;
            const formData = new FormData();
            formData.append('timer_id', timerId);
            fetch('controllers/TimerController.php/get_elapsed_time', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('timerDisplay').innerText = data.elapsed;
                    } else {
                        console.error(data.error);
                    }
                });
        }

        // Inicia um novo timer
        function startTimer() {
            const formData = new FormData(document.getElementById('startForm'));
            fetch('controllers/TimerController.php/start_timer', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        timerId = data.timerid;
                        // Atualiza o tempo do timer iniciado a cada segundo
                        timerInterval = setInterval(updateElapsedTime, 1000);
                        // Atualiza a lista de timers ativos
                        loadRunningTimers();
                    } else {
                        alert('Erro: ' + data.error);
                    }
                });
        }

        // Para o timer iniciado pelo formulário
        function stopTimer() {
            if (!timerId) return;
            const formData = new FormData();
            formData.append('timer_id', timerId);
            fetch('controllers/TimerController.php/end_timer', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('startButton').style.display = 'inline';
                        clearInterval(timerInterval);
                        timerInterval = null;
                        updateElapsedTime();
                        timerId = null;
                        loadRunningTimers();
                    } else {
                        alert('Erro: ' + data.error);
                    }
                });
        }

        // Para um timer a partir da lista (por ID)
        function stopTimerById(id) {
            const formData = new FormData();
            formData.append('timer_id', id);
            fetch('controllers/TimerController.php/end_timer', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadRunningTimers();
                        loadClosedTimers();
                    } else {
                        alert('Erro: ' + data.error);
                    }
                });
        }

        // Carrega e exibe a lista de timers em execução
        function loadRunningTimers() {
            const formData = new FormData();
            fetch('controllers/TimerController.php/get_running_timers', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.timers.length > 0) {
                            data.timers.forEach((item, index) => {
                            for (const key in item) {
                            formData.append(`timers[${index}][${key}]`, item[key]);
                            }
                        });
                        fetch('executiontimers.php', { method: 'POST', body: formData })
                            .then(response => response.text())
                            .then(htmlexecution => {
                                document.getElementById('runningTimersList').innerHTML = htmlexecution;
                                initTimers();
                            })
                            .catch(error => console.error('Erro ao carregar o HTML:', error));
                        } else {
                            htmlexecution = 'Nenhum timer em execução.';
                        }
                        document.getElementById('runningTimersList').innerHTML = htmlexecution;
                    } else {
                        console.error(data.error);
                    }
                });
        }

        // Inicializa os campos Select2 após o carregamento do documento
        $(document).ready(function() {
            // Campo Projeto
            $('#project_select').select2({
                placeholder: 'Selecione ou crie um projeto',
                tags: true, // Permite criar novos itens
                ajax: {
                    url: 'controllers/ProjectsController.php',
                    dataType: 'json',
                    processResults: function(data) {
                        return { results: data };
                    }
                },
                createTag: function (params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newOption: true
                    };
                }
            });

            // Campo Tag
            $('#tag_select').select2({
                placeholder: 'Selecione ou crie uma tag',
                tags: true,
                ajax: {
                    url: 'controllers/TagsController.php',
                    dataType: 'json',
                    processResults: function(data) {
                        return { results: data };
                    }
                },
                createTag: function (params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newOption: true
                    };
                }
            });
        });

        // Atualiza periodicamente a lista de timers ativos
        window.onload = function() {
            loadRunningTimers();
            loadClosedTimers();
        }
        function initTimers() {
            // Função para formatar o tempo
            function formatTime(seconds) {
                var hours = Math.floor(seconds / 3600);
                var minutes = Math.floor((seconds % 3600) / 60);
                var secs = seconds % 60;
                return (hours < 10 ? '0' : '') + hours + ':' + (minutes < 10 ? '0' : '') + minutes + ':' + (secs < 10 ? '0' : '') + secs;
            }
            let timeseconds = 0;
            // Atualiza o tempo de cada timer
            $('.elapsed-time').each(function() {
                var startTime = $(this).data('start-time');
                var $this = $(this);
                var $circle = $(this).closest('li').find('.circle-progress');
                // Função de atualização do cronómetro
                setInterval(function() {
                    var currentTime = Math.floor(Date.now() / 1000); // Tempo atual em segundos
                    var elapsedTime = currentTime - startTime; // Tempo decorrido desde o início
                    var totalTime = 60; // Tempo total em segundos (1 hora)
                    timeseconds++
                    // Atualiza o texto com o tempo formatado
                    $this.text(formatTime(elapsedTime));
                    
                    // Calcula o progresso da barra circular
                    var progress = (timeseconds ) * 100;
                    $circle.css('stroke-dashoffset', 100 - progress); // Atualiza a barra de progresso
                }, 1000); // Atualiza a cada segundo
            });
        }
    </script>
</head>
<body class="bg-light text-dark" style="font-family: 'Poppins', sans-serif;">
    <div class="container mt-5">
        <h2 class="mb-4 text-center fw-bold">Iniciar Timer</h2>
        <form id="startForm" class="card p-4 shadow-lg border-0 rounded-4 bg-white" onsubmit="event.preventDefault(); startTimer();">
            <div class="mb-3">
                <label class="form-label">Nome do Timer:</label>
                <input type="text" name="name" class="form-control shadow-sm rounded-3" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Projeto:</label>
                <select id="project_select" name="project_name" class="form-select shadow-sm rounded-3"></select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tag:</label>
                <select id="tag_select" name="tag_name" class="form-select shadow-sm rounded-3"></select>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="billable" id="billable">
                <label class="form-check-label" for="billable">Faturável</label>
            </div>
            <button type="submit" class="btn btn-primary w-100 shadow-sm rounded-3">Iniciar Timer</button>
        </form>

        <div class="mt-5 card p-4 shadow-lg border-0 rounded-4 bg-white">
            <h2 class="text-center fw-bold">Timers em Execução</h2>
            <div id="runningTimersList" class="text-center">Carregando timers...</div>
        </div>

        <div class="mt-4 card p-4 shadow-lg border-0 rounded-4 bg-white">
            <h2 class="mb-4 text-center text-primary">Timers Fechados</h2>
            <div id="closedTimersList" class="text-center">Carregando timers fechados...</div>
        </div>
    </div>
</body>
</html>