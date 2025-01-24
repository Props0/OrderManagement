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
    <div class="form-container">
        <h1>Gestor de Encomendas</h1>
        <form id="excelForm" class="form-group">
            <div class="ordergroup" id="ordergroup">
                <h4 class="form-header">Encomenda N1</h4>
                <input name="orders[0][Nome do Destinatário]" id="name"  placeholder='Nome do Destinatário'></input>    
                <input name="orders[0][Morada do Destinatário linha 1]" id="address"  rows="5" cols="5" placeholder='Morada do Destinatário linha 1'></input>
                <input name="orders[0][Morada do Destinatário linha 2]" id="address2"  rows="5" cols="5" placeholder='Morada do Destinatário linha 2'></input>
                <input name="orders[0][Código Postal Destinatário]" id="postalcode"  placeholder='Código Postal Destinatário'></input>
                <input name="orders[0][Localidade Postal do Destinatário]" id="postalcode2"  placeholder='Localidade Postal do Destinatário'></input>
                <input name="orders[0][Localidade do Destinatário]" id="city"  placeholder='Localidade do Destinatário'></input>
                <input name="orders[0][Região Destinatário]" id="region"  placeholder='Região Destinatário'></input>
                <input name="orders[0][Pais de Destino]" id="country" placeholder='Pais de Destino'></input>
                <input name="orders[0][Endereço Email Destinatário]" id="email"  placeholder='Endereço Email Destinatário'></input>
            </div>
            <br>
        </form>
        <button type="button" onclick="addshipmentcontact()">Adicionar contacto</button>
    
        <button type="button" onclick="submit()">Submeter</button>
    </div>
    
    <div id="relatorio"></div>

    <script>
        var contactnumber = 1;
        async function addshipmentcontact() {
            $('#excelForm').append('<div class="ordergroup" id="ordergroup'+(contactnumber+1)+'"><h4 class="form-header">Encomenda N'+(contactnumber+1)+"</h4>")
            $('#ordergroup :input').each(function() {
                var copy = $(this).clone()
                $('#excelForm').append(copy.attr('id', $(this).attr('id')+contactnumber).attr('name', "orders["+contactnumber+"]["+$(this).attr('name').split("[")[2]))
            });
            $('#excelForm').append("</div>")
            contactnumber++;
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
                    body:  JSON.stringify(formData)
                });
                const result = await response.text();
                alert($.parseJSON(result).message);
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
