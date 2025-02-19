<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Encomendas</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container mt-5">
        <div class="card shadow p-4">
            <h1 class="text-center mb-4 text-primary">Gestor de Encomendas</h1>
            <form id="excelForm">
                <div class="ordergroup border rounded p-3 mb-3" id="ordergroup">
                    <h4 class="form-header text-secondary" id="titleorder">Encomenda N1</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Nome do Destinatário]" placeholder="Nome do Destinatário">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Morada do Destinatário linha 1]" placeholder="Morada linha 1">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Morada do Destinatário linha 2]" placeholder="Morada linha 2">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Código Postal Destinatário]" placeholder="Código Postal">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Localidade Postal do Destinatário]" placeholder="Localidade Postal">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Localidade do Destinatário]" placeholder="Localidade">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Região Destinatário]" placeholder="Região">
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" name="orders[0][Pais de Destino]" placeholder="País">
                        </div>
                        <div class="col-12">
                            <input class="form-control" name="orders[0][Endereço Email Destinatário]" placeholder="Email">
                        </div>
                    </div>
                </div>
            </form>
            <button class="btn btn-success w-100 my-2" type="button" onclick="addshipmentcontact()">Adicionar Encomenda</button>
            <button class="btn btn-primary w-100" type="button" onclick="submit()">Submeter</button>
        </div>
    </div>

    <script>
        var contactnumber = 0;
        async function addshipmentcontact() {
            contactnumber++;
            neworder = $('#ordergroup').clone();
            neworder.find(':input').each(function() {
                $(this).attr('id', $(this).attr('id')+contactnumber).attr('name', "orders["+contactnumber+"]["+$(this).attr('name').split("[")[2])
            });
            neworder.find("#titleorder").html("Encomenda N"+(contactnumber+1));
            $('#excelForm').append(neworder)
        }
        async function submit() {
            let formData = {};
            $('#excelForm :input')
            .each(function () {
                const name = $(this).attr('name');
                const value = $(this).val();
                setDeepValue(formData, name, value);
            });
            try {
                const response = await fetch('controllers/ExcelController.php/writeToExcel', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    const disposition = response.headers.get('Content-Disposition');
                    let fileName = 'relatorio.xlsx';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                        const matches = filenameRegex.exec(disposition);
                        if (matches != null && matches[1]) {
                            fileName = matches[1].replace(/['"]/g, '');
                        }
                    }
                    a.download = fileName;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                } else {
                    const text = await response.text();
                    const result = JSON.parse(text);
                    alert(result.message);
                }
            } catch (error) {
                console.log(error);
                alert("Erro ao enviar data.");
            }
        }
        function setDeepValue(obj, path, value) {
            let keys = path.replace(/\]/g, '').split(/\[/);
            let current = obj;

            keys.forEach((key, index) => {
                if (index === keys.length - 1) {
                    current[key] = value;
                } else {
                    if (!current[key]) {
                        current[key] = isNaN(keys[index + 1]) ? {} : [];
                    }
                    current = current[key];
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
