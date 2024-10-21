<?php
session_start();
require_once '../inc/conexion.php';
require_once '../inc/funciones.php';

$errores = ['nombre' => '', 'email' => '', 'password' => '', 'exito' => ''];

$nombre = '';
$email = '';
$password = '';
$rol = 'invitado'; // Rol predeterminado
$rutaImagen = null; // Inicializar la variable para la imagen

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar_dato($_POST['nombre']);
    $email = limpiar_dato($_POST['email']);
    $password = $_POST['password'];
    $rol = limpiar_dato($_POST['rol']); // Recibe el rol seleccionado

    // Validaciones
    if (empty($nombre)) {
        $errores['nombre'] = 'El nombre es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido.';
    }
    if (strlen($password) < 6) {
        $errores['password'] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    // Verificar si el email ya existe en la base de datos
    $sqlVerificacion = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
    $stmtVerificacion = $conexion->prepare($sqlVerificacion);
    $stmtVerificacion->bindParam(':email', $email);
    $stmtVerificacion->execute();
    $emailExiste = $stmtVerificacion->fetchColumn();

    if ($emailExiste) {
        $errores['email'] = 'El correo electrónico ya está registrado.';
    }

    // Manejo de la carga de archivos
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = $_FILES['imagen']['name'];
        $rutaTemporal = $_FILES['imagen']['tmp_name'];
        $directorioDestino = '../uploads/'; // Asegúrate de que esta carpeta exista y tenga permisos de escritura

        // Mover el archivo subido a la carpeta de destino
        if (move_uploaded_file($rutaTemporal, $directorioDestino . $nombreArchivo)) {
            // Guardar la URL o la ruta en la base de datos
            $rutaImagen = $directorioDestino . $nombreArchivo;
        } else {
            $errores['exito'] = 'Error al mover el archivo.';
        }
    } else {
        $rutaImagen = null; // O asigna un valor predeterminado si no hay imagen
    }

    // Si no hay errores, proceder con el registro
    if (empty(array_filter($errores))) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, email, password, rol, url_imagen) VALUES (:nombre, :email, :password, :rol, :url_imagen)";
        $stmt = $conexion->prepare($sql);
        
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':url_imagen', $rutaImagen); // Cambiado de :imagen a :url_imagen
        

        if ($stmt->execute()) {
            $errores['exito'] = 'Usuario registrado exitosamente.';
        } else {
            echo "Error al registrar el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        body{
            margin: 0;
        }
        .caja{
            display: grid;
            place-items: center;
            min-height: 100vh;
            background-color: #f0f0f0;
        }

        header{
            display: flex;
            justify-content: flex-end;
            align-items: center;
            height: 50px;
        }

        a{
            padding-right: 20px;
            text-decoration: none;
            color: black;
            font-size: 27px;
        }
        label{
            font-size: 20px;
            
        }

        form{
            width: 100%;
        }

        h2{
            text-align: center;
        }

        .exito{
            text-align: center;
            color: green;
            font-weight: bold;
        }

        input{
            width: -webkit-fill-available;
        }
        #rol{
            font-weight: bold;
        }

        .error {
            color: red;
            font-size: 0.9em;
        }
        .container {
            font-size: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            width: 100%;
            padding-bottom: 10px;
        }
        /*caja*/
        
        /*rol*/
        .label-container {
            border: 2px solid #d6d6d6;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            padding: 10px;
            width: 50%; /* Para ajustar el tamaño del label */
                  
        }
        .label-container2 {
            border: 2px solid #d6d6d6;
            text-align: center;
            font-size: 14px;
            height: 23px;
            font-weight: bold;
            padding: 10px;
            width: 50%; /* Para ajustar el tamaño del label */
            align-items: center;
        }
        .select-container {
            height: 23px;
            margin-left: 20px;
            width: 50%;
            border: 2px solid #d6d6d6; /* Borde para el select */
            padding: 10px;
            text-align: center; /* Centra el contenido dentro del contenedor */
            font-weight: bold;
        }

       /* Estilo para inputs, selects y botones */
        
        .btnarchivos {
            border: 1px solid black;
            background-color: transparent; 
            color: black;
            cursor: pointer; /* Cambia el cursor en forma de mano cuando se pasa por encima*/ 
            padding: 0; /* Añade espacio interno al botón */
            width: 100%; 
            height: 23px;
            font-size: 14px; 
            font-weight: bold; /* Texto en negrita para todos */
            text-align: center; /* Centra el texto dentro del botón */
            border-radius: 5px;

            }
            .file-input {
                display: none; /* Oculta completamente el input de archivo */
            }
    </style>
</head>
<body>
    <header>
        <a href="../index.php">Index</a>
        <a href="login.php">Login</a>
    </header>

    <div class="caja">
        <form method="post" enctype="multipart/form-data"> <!-- Asegúrate de incluir enctype -->
            <h2>Registro de Usuario</h2>
            <?php if (!empty($errores['exito'])): ?>
                <p class="exito"><?php echo $errores['exito']; ?></p>
            <?php endif; ?>
            
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($nombre); ?>">
            <?php if (!empty($errores['nombre'])): ?>
                <p class="error"><?php echo $errores['nombre']; ?></p>
            <?php endif; ?>
        
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
            <?php if (!empty($errores['email'])): ?>
                <p class="error"><?php echo $errores['email']; ?></p>
            <?php endif; ?>
        
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password">
            <?php if (!empty($errores['password'])): ?>
                <p class="error"><?php echo $errores['password']; ?></p>
            <?php endif; ?>

            <div class="container">
                <div class="label-container">
                    Rol:
                </div>
                <div class="select-container">
                    <select id="rol" name="rol">
                        <option value="invitado">Invitado</option>
                        <option value="admin">Administrador</option>
                        <option value="usuario">Usuario</option>
                    </select>
                </div>
            </div>
           
            <div class="container">
                <div class="label-container2">
                    Imagen de perfil:
                </div>
                <div class="select-container">
                    <input type="file" id="imagen" name="imagen" class="file-input" accept="image/*">
                    <button type="button" class="btnarchivos" onclick="uploadFile()">Elegir archivo</button>
                </div>
            </div>

            <button type="submit">Registrar</button>
        </form>
    </div>
    <script>
        document.querySelector('.btnarchivos').addEventListener('click', function() {
            document.querySelector('#imagen').click(); // Simula un clic en el input de archivo oculto
        });
    </script>
</body>
</html>