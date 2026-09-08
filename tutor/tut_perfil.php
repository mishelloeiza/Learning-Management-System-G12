<?php
	//conexion
	include("../conexion.php");

	//Verificar sesion
	session_start();

	//Si no hay sesion iniciada o si el rol no es 2
	if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2') {
	    header("Location: ../login.php");
	    exit();
	}

	//obtener id del usuario
	$id = $_SESSION['id'];

	//hacer consulta
	function cargardatos(){
		include("../conexion.php");
		$id = $_SESSION['id'];
		
		$sql = "SELECT nombre, apellido, correo, telefono, contrasena FROM usuarios WHERE id_usuario = $id";
		$resultado = $cn->query($sql);
		$fila = $resultado->fetch_assoc();

		return $fila; 
	}
	
	//Cambiar datos 
	if (isset($_POST['btnG'])) {
		$nombre = trim($_POST['nombre']);
		$apellido = trim($_POST['apellido']);
		$correo = trim($_POST['correo']);
		$telefono = trim($_POST['telefono']);

		//Validaciones de campos
		if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			echo "<script>alert('Correo inválido')</script>";
		} else if (strlen($telefono) > 9 || !ctype_digit($telefono)) {
			echo "<script>alert('Teléfono inválido')</script>";
		} else {
			try {
				//Sentencia SQL preparada
				$stmt = $cn->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id_usuario = ?");
				$stmt->bind_param("ssssi", $nombre, $apellido, $correo, $telefono, $id);
				$stmt->execute();
				echo "<script>alert('Información actualizada correctamente')</script>";
			} catch (mysqli_sql_exception $error) {
				if ($error->getCode() == 1062) {
					echo "<script>alert('Ese correo ya está registrado')</script>";
				} else {
					echo "<script>alert('No se puedo crea la cuenta, intenta de nuevo')</script>";
				}
			}
		}
	}
 
 	$act = cargardatos();
	//obtener valores
	$nombre = $act['nombre'];
	$apellido = $act['apellido'];
	$correo = $act['correo'];
	$telefono = $act['telefono'];
	
	//Cambiar contraseña
	if (isset($_POST['btnC'])) {
		$contrasena_act = $_POST['contrasena_act'];
		$contrasena_new = $_POST['contrasena_new'];

		//Sentencia SQL preparada
		$stmt = $cn->prepare("SELECT contrasena FROM usuarios WHERE id_usuario = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$arrayb = $stmt->get_result()->fetch_assoc();

		if ($arrayb !== null && password_verify($contrasena_act, $arrayb['contrasena'])) {
   			if (strlen($contrasena_new < 8)) {
				echo "<script>alert('La nueva contraseña debe tener al menos 8 caracteres')</script>";
			}else {
				//Cifrar la contraseña
				$cifrada = password_hash($contrasena_new, PASSWORD_DEFAULT);
				// Actualizar contraseña
            	$stmt = $cn->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?");
            	$stmt->bind_param("si", $cifrada, $id);
            	if ($stmt->execute()){
            		echo "<script>alert('Contraseña actualizada correctamente')</script>";
            	}else {
            		echo "<script>alert('Contraseña no actualizada')</script>";
            	}	
			}
		} else {
			echo "<script>alert('La contraseña actual es incorrecta')</script>";
		}
	}

	//Eliminar cuenta
	if(isset($_POST['btnElim'])){
		$stmt = $cn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
		$stmt->bind_param("i", $id);
		if($stmt->execute()){
			session_start();
			$_SESSION = [];
			session_destroy();
			header("Location: ../login.php");
			exit(); 
		}else {
			echo "<script>alert('Cuenta no eliminada')</script>";
		}
	}
	

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<header>
		<nav>
			<ul>
				<li>
					<a href="../cerrar_sesion.php">Cerrar sesión</a>
				</li>
			</ul>
		</nav>	
	</header>
	
	<h1>Mi perfil</h1>
	<form action="tut_perfil.php" method="post">
		<label for="nombre">Nombre:</label>
		<input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" disabled required>
		<label for="apellido">Apellido:</label>
		<input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($apellido) ?>" disabled required>
		<label for="correo">Correo</label>
		<input type="email" id="correo" name="correo" value="<?= htmlspecialchars($correo) ?>" disabled required>
		<label for="telefono">Teléfono</label>
		<input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($telefono) ?>" disabled required>
		<button type="submit" name="btnE" id="btnE">Editar Datos</button>
		<button type="submit" name="btnG" id="btnG" disabled >Guardar Cambios</button>
	</form>

	<h2>Cambiar contraseña</h2>
	<form action="tut_perfil.php" method="post">
		<label for="contrasena_act">Contraseña Actual:</label>
		<input type="password" name="contrasena_act" placeholder="Ingrese su contraseña actual" required>
		<label for="contrasena_new">Nueva Contraseña:</label>
		<input type="password" name="contrasena_new" placeholder="Ingrese su nueva contraseña" required>
		<button type="submit" name="btnC" id="btnC">Cambiar contraseña</button>
	</form>

	<form action="tut_perfil.php" method="post" 
	onsubmit="return confirm('¿Estás seguro de que deseas eliminar tu cuenta?');">
	    <button type="submit" name="btnElim">
	        Eliminar cuenta
	    </button>
	</form>

	<script>
		const Editar = document.getElementById("btnE");
		const Guardar = document.getElementById("btnG");
		const inputs = document.querySelectorAll("input"); 

		Editar.addEventListener("click", function(){
			inputs.forEach(function(input) {
	            input.disabled = false;
	        });
	        Guardar.disabled = false;

        	Editar.disabled = true;
		});
	</script>
	
</body>
</html>
