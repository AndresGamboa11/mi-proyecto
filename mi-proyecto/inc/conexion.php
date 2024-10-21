<?php
try{
    $host= '127.0.0.1';
    $dbname = 'miproyecto';
    $user = 'root';
    $passwordDB = '';
    $port ='3306';

   $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $passwordDB);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}


?>