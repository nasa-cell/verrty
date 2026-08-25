/**
 * ORDENIA IA — modal de confirmación propio (reemplaza el confirm()
 * nativo del navegador, que se ve genérico y no combina con el resto
 * del diseño). Cualquier <form> con el atributo data-confirmar="..."
 * muestra este modal antes de enviarse de verdad.
 */
(function () {
  'use strict';

  const overlay = document.getElementById('modal-confirmacion');
  if (!overlay) return;

  const mensajeEl = document.getElementById('modal-confirmacion-mensaje');
  const btnAceptar = document.getElementById('modal-confirmacion-aceptar');
  const btnCancelar = document.getElementById('modal-confirmacion-cancelar');

  let formPendiente = null;

  function abrir(mensaje) {
    mensajeEl.textContent = mensaje;
    overlay.hidden = false;
    // requestAnimationFrame para que la transición de entrada se vea
    // (si se agrega la clase en el mismo frame que se quita "hidden",
    // el navegador no anima el cambio).
    requestAnimationFrame(() => overlay.classList.add('visible'));
    btnAceptar.focus();
  }

  function cerrar() {
    overlay.classList.remove('visible');
    setTimeout(() => {
      overlay.hidden = true;
    }, 200);
    formPendiente = null;
  }

  document.querySelectorAll('form[data-confirmar]').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
      evento.preventDefault();
      formPendiente = formulario;
      abrir(formulario.dataset.confirmar);
    });
  });

  btnCancelar.addEventListener('click', cerrar);

  overlay.addEventListener('click', (evento) => {
    if (evento.target === overlay) cerrar();
  });

  document.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape' && !overlay.hidden) cerrar();
  });

  btnAceptar.addEventListener('click', () => {
    if (formPendiente) {
      const form = formPendiente;
      formPendiente = null;
      form.submit();
    }
    cerrar();
  });
})();
