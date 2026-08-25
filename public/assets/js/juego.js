/**
 * ORDENIA IA — lógica del juego "ordenar de mayor a menor".
 * Reordenamiento por arrastrar (HTML5 Drag & Drop en escritorio) y por
 * toque/click (seleccionar + intercambiar) para que funcione igual en
 * celular y tablet. Incluye el temporizador y el envío de la respuesta.
 */
(function () {
  'use strict';

  const zona = document.getElementById('zona-fichas');
  if (!zona) return;

  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
  const btnComprobar = document.getElementById('btn-comprobar');
  const resultado = document.getElementById('resultado-mensaje');
  const temporizadorEl = document.getElementById('temporizador');
  const ejercicioId = zona.dataset.ejercicioId;

  // Arranca desde el tiempo REAL restante calculado en el servidor
  // (TimerService, a partir de fecha_inicio) — no desde el máximo. Así,
  // si la página se recarga a mitad de un ejercicio, el temporizador
  // sigue contando desde donde iba en vez de reiniciarse.
  let segundosRestantes = parseInt(zona.dataset.segundosRestantes, 10);
  let bloqueado = false;
  let fichaSeleccionada = null;
  let fichaArrastrada = null;

  function actualizarTemporizador() {
    temporizadorEl.textContent = String(segundosRestantes).padStart(2, '0') + 's';
    temporizadorEl.classList.toggle('urgente', segundosRestantes <= 10);
  }

  actualizarTemporizador();

  const intervalo = setInterval(() => {
    if (bloqueado) return;

    segundosRestantes--;
    actualizarTemporizador();

    if (segundosRestantes <= 0) {
      clearInterval(intervalo);
      tiempoAgotado();
    }
  }, 1000);

  function tiempoAgotado() {
    bloqueado = true;
    resultado.textContent = '⏰ Tiempo terminado.';
    resultado.className = 'resultado-mensaje incorrecto';
    document.querySelectorAll('.ficha-numero').forEach((f) => (f.draggable = false));
    btnComprobar.disabled = true;
    setTimeout(() => window.location.reload(), 1800);
  }

  function intercambiar(fichaA, fichaB) {
    const marcador = document.createComment('marcador');
    fichaA.parentNode.insertBefore(marcador, fichaA);
    fichaB.parentNode.insertBefore(fichaA, fichaB);
    marcador.parentNode.insertBefore(fichaB, marcador);
    marcador.remove();
  }

  zona.addEventListener('click', (evento) => {
    if (bloqueado) return;
    const ficha = evento.target.closest('.ficha-numero');
    if (!ficha) return;

    if (!fichaSeleccionada) {
      fichaSeleccionada = ficha;
      ficha.classList.add('sobre-objetivo');
      return;
    }

    if (fichaSeleccionada === ficha) {
      ficha.classList.remove('sobre-objetivo');
      fichaSeleccionada = null;
      return;
    }

    intercambiar(fichaSeleccionada, ficha);
    fichaSeleccionada.classList.remove('sobre-objetivo');
    fichaSeleccionada = null;
  });

  zona.addEventListener('dragstart', (evento) => {
    const ficha = evento.target.closest('.ficha-numero');
    if (!ficha) return;
    fichaArrastrada = ficha;
    ficha.classList.add('arrastrando');
  });

  zona.addEventListener('dragend', (evento) => {
    const ficha = evento.target.closest('.ficha-numero');
    if (ficha) ficha.classList.remove('arrastrando');
    fichaArrastrada = null;
  });

  zona.addEventListener('dragover', (evento) => {
    evento.preventDefault();
    const sobre = evento.target.closest('.ficha-numero');
    if (sobre) sobre.classList.add('sobre-objetivo');
  });

  zona.addEventListener('dragleave', (evento) => {
    const sobre = evento.target.closest('.ficha-numero');
    if (sobre) sobre.classList.remove('sobre-objetivo');
  });

  zona.addEventListener('drop', (evento) => {
    evento.preventDefault();
    const objetivo = evento.target.closest('.ficha-numero');
    if (objetivo) objetivo.classList.remove('sobre-objetivo');
    if (!objetivo || !fichaArrastrada || objetivo === fichaArrastrada) return;
    intercambiar(fichaArrastrada, objetivo);
  });

  function lanzarConfeti() {
    const contenedor = document.createElement('div');
    contenedor.className = 'confeti';
    const colores = ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed'];
    for (let i = 0; i < 40; i++) {
      const pieza = document.createElement('span');
      pieza.style.left = Math.random() * 100 + 'vw';
      pieza.style.background = colores[i % colores.length];
      pieza.style.animationDelay = Math.random() * 0.4 + 's';
      contenedor.appendChild(pieza);
    }
    document.body.appendChild(contenedor);
    setTimeout(() => contenedor.remove(), 1800);
  }

  btnComprobar.addEventListener('click', async () => {
    if (bloqueado) return;
    bloqueado = true;
    clearInterval(intervalo);
    btnComprobar.classList.add('btn--loading'); // esperando la respuesta del servidor

    const respuesta = Array.from(zona.querySelectorAll('.ficha-numero')).map((f) => parseFloat(f.dataset.valor));

    try {
      const res = await fetch(btnComprobar.dataset.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
          ejercicio_id: ejercicioId,
          respuesta,
        }),
      });
      const datos = await res.json();

      if (datos.error) {
        resultado.textContent = datos.error;
        resultado.className = 'resultado-mensaje incorrecto';
        bloqueado = false;
        btnComprobar.classList.remove('btn--loading');
        return;
      }

      if (datos.correcta) {
        resultado.textContent = `✅ ¡Correcto! +${datos.puntaje} puntos — ${datos.frase}`;
        resultado.className = 'resultado-mensaje correcto';
        lanzarConfeti();
      } else {
        resultado.textContent = `❌ Incorrecto. La respuesta era: ${datos.respuesta_correcta.join(' - ')}. ${datos.frase}`;
        resultado.className = 'resultado-mensaje incorrecto';
      }

      setTimeout(() => window.location.reload(), 2600);
    } catch (error) {
      resultado.textContent = 'No se pudo enviar la respuesta. Intente nuevamente.';
      resultado.className = 'resultado-mensaje incorrecto';
      bloqueado = false;
      btnComprobar.classList.remove('btn--loading');
    }
  });
})();
