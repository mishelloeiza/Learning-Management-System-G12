<?php require_once __DIR__ . '/config/RedirectAuthenticated.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="CSS/auth.css">
	<link rel="stylesheet" href="CSS/panel.css">
	<link rel="stylesheet" href="CSS/navbar.css">

</head>

<body>
	<header class="site-header">
		<nav class="navbar" aria-label="Navegación principal">
            <span class="navbar__brand" aria-label="LMS G12"><span class="navbar__brand-mark" aria-hidden="true">G12</span><span class="navbar__brand-name">Campus<span>Virtual</span></span></span>
			<input class="navbar__check" id="navToggle" type="checkbox" aria-label="Mostrar menú de navegación">
			<label class="navbar__toggle" for="navToggle" aria-label="Abrir o cerrar menú">
			<span class="navbar__bars" aria-hidden="true"></span>
			</label>
			<ul class="navbar__menu" id="menuPrincipal">
				<li><a href="./estudiante/stu_crear_cuenta">Crear Cuenta Estudiante</a></li>
				<li><a class="navbar__cta" href="./login">Iniciar Sesion</a></li>
			</ul>
		</nav>
	</header>

	<main class="landing-main">
        <section class="landing-hero" aria-labelledby="landing-title">
            <div class="landing-copy">
                <span class="eyebrow">PLATAFORMA ACADÉMICA · G12</span>
                <h1 id="landing-title">Aprende, comparte y <em>avanza.</em></h1>
                <p>Tu espacio para organizar tus tutorías, conectar con docentes y seguir creciendo a tu ritmo.</p>
                <div class="landing-actions">
                    <a class="landing-button" href="./login">Ingresar al campus <span aria-hidden="true">→</span></a>
                    <a class="landing-link" href="./estudiante/stu_crear_cuenta">Crear una cuenta</a>
                </div>
            </div>
            <div class="landing-visual" aria-hidden="true">
                <div class="landing-orb landing-orb--one"></div>
                <div class="landing-orb landing-orb--two"></div>
                <div class="landing-visual-inner"><span class="landing-visual-icon">G12</span><strong>Tu próximo logro comienza aquí.</strong><small>Educación que conecta</small></div>
            </div>
        </section>
    </main>

</body>

</html>
