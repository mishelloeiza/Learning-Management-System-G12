/* Navbar: menú desplegable cuando los enlaces no caben, página activa y cierre con Escape / clic fuera. */
(function () {
	"use strict";

	function iniciar() {
		var navbar = document.querySelector(".navbar");
		if (!navbar) return;

		var toggle = navbar.querySelector(".navbar__toggle");
		var menu = navbar.querySelector(".navbar__menu");
		if (!toggle || !menu) return;

		/* Marca la página actual */
		var actual = location.pathname.replace(/\/+$/, "").split("/").pop() || "index.php";
		menu.querySelectorAll("a[href]").forEach(function (enlace) {
			var destino = new URL(enlace.getAttribute("href"), location.href).pathname.split("/").pop();
			if (destino === actual) enlace.setAttribute("aria-current", "page");
		});

		function abrir(estado) {
			navbar.classList.toggle("is-open", estado);
			toggle.setAttribute("aria-expanded", String(estado));
			toggle.setAttribute("aria-label", estado ? "Cerrar menú" : "Abrir menú");
		}

		/* Colapsa solo si los enlaces no caben en una fila */
		function ajustar() {
			navbar.classList.remove("is-collapsed");
			var noCabe = navbar.scrollWidth > navbar.clientWidth + 1;
			navbar.classList.toggle("is-collapsed", noCabe);
			if (!noCabe) abrir(false);
		}

		toggle.addEventListener("click", function () {
			abrir(!navbar.classList.contains("is-open"));
		});

		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" && navbar.classList.contains("is-open")) {
				abrir(false);
				toggle.focus();
			}
		});

		document.addEventListener("click", function (e) {
			if (navbar.classList.contains("is-open") && !navbar.contains(e.target)) abrir(false);
		});

		var pendiente;
		function programar() {
			cancelAnimationFrame(pendiente);
			pendiente = requestAnimationFrame(ajustar);
		}

		window.addEventListener("resize", programar);
		window.addEventListener("orientationchange", programar);
		if (document.fonts && document.fonts.ready) document.fonts.ready.then(ajustar);

		ajustar();
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", iniciar);
	} else {
		iniciar();
	}
})();
