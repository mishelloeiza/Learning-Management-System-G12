<?php
	session_start();

	if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== '2') {
	    header("Location: ../prin_dashboard.php");
	    exit();
	}
?>
