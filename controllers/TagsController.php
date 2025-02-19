<?php
$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$dbname     = "timer";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$tags = [];
$result = $conn->query("SELECT name FROM tag");
while ($row = $result->fetch_assoc()){
    $tags[] = ['id' => $row['name'], 'text' => $row['name']];
}
echo json_encode($tags);
$conn->close();
?>
