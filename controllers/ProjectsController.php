<?php
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$dbname     = "timer";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$projects = [];
$result = $conn->query("SELECT name FROM project");
while ($row = $result->fetch_assoc()){
    // Note que usamos o nome como id, mas você pode usar o id do banco se preferir
    $projects[] = ['id' => $row['name'], 'text' => $row['name']];
}
echo json_encode($projects);
$conn->close();
?>