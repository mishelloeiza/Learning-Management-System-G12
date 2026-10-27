/*
 * Tablas responsive sin scroll horizontal.
 * Copia el texto de cada <th> en el atributo data-label de las celdas de su
 * columna, para que el CSS (responsive/mobile-tables.css) pueda mostrarlas como
 * tarjetas apiladas en pantallas pequeñas. Las filas se generan por fetch, por
 * eso se observa el DOM y se re-etiqueta cuando cambia.
 */
(function () {
  function etiquetar(tabla) {
    var cabeceras = Array.prototype.map.call(
      tabla.querySelectorAll("thead th"),
      function (th) { return th.textContent.trim(); }
    );

    tabla.querySelectorAll("tbody tr").forEach(function (tr) {
      var columna = 0;
      Array.prototype.forEach.call(tr.children, function (td) {
        if (td.tagName !== "TD") return;
        var span = td.colSpan || 1;
        if (span > 1 || !cabeceras[columna]) {
          td.removeAttribute("data-label"); // fila de aviso ("No se encontraron...")
        } else {
          td.setAttribute("data-label", cabeceras[columna]);
        }
        columna += span;
      });
    });
  }

  function etiquetarTodas() {
    document.querySelectorAll("table").forEach(etiquetar);
  }

  var pendiente = false;
  function programar() {
    if (pendiente) return;
    pendiente = true;
    requestAnimationFrame(function () {
      pendiente = false;
      etiquetarTodas();
    });
  }

  function iniciar() {
    etiquetarTodas();
    new MutationObserver(programar).observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciar);
  } else {
    iniciar();
  }
})();
