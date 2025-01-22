<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Excel</title>
</head>
<body>
    <h1>Gestor de Excel</h1>
    <form id="excelForm">
        <textarea name="dados" id="dados" rows="10" cols="50" placeholder='{"dados": {"nome": "João", "idade": 25}}'></textarea>
        <br>
        <button type="button" onclick="enviarDados()">Enviar Dados</button>
    </form>
    <h2>Relatório Atual</h2>
    <div id="relatorio"></div>

    <script>
        async function carregarRelatorio() {
            try {
                const response = await fetch('route.php');
                const data = await response.json();
                if (data.dados) {
                    document.getElementById('relatorio').innerHTML = `<pre>${JSON.stringify(data.dados, null, 2)}</pre>`;
                } else {
                    document.getElementById('relatorio').innerText = data.error || "Erro ao carregar o relatório.";
                }
            } catch (error) {
                document.getElementById('relatorio').innerText = "Erro ao carregar o relatório.";
            }
        }

        async function enviarDados() {
            const dados = document.getElementById('dados').value;

            try {
                const response = await fetch('route.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: dados
                });
                const result = await response.json();
                alert(result.message || result.error);
                carregarRelatorio();
            } catch (error) {
                console.log(error);
                alert("Erro ao enviar dados.");
            }
        }

        // Carregar o relatório ao iniciar
        carregarRelatorio();
    </script>
</body>
</html>
