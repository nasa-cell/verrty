/**
 * ORDENIA IA — buscador de alumnos en vivo.
 *
 * Reemplaza los <select> gigantes con todos los alumnos (que con 400 o
 * 4000 alumnos se vuelven lentos de cargar y horribles de usar) por un
 * campo de texto que busca en el servidor mientras se escribe y nunca
 * trae más de ~15 resultados a la vez.
 *
 * Uso: window.crearBuscadorAlumno({ inputId, resultadosId, hiddenId, url }).
 */
window.crearBuscadorAlumno = function crearBuscadorAlumno(config) {
  const input = document.getElementById(config.inputId);
  const resultados = document.getElementById(config.resultadosId);
  const hidden = document.getElementById(config.hiddenId);

  if (!input || !resultados || !hidden) return;

  let temporizadorBusqueda = null;
  let controladorActual = null;

  function ocultarResultados() {
    resultados.innerHTML = '';
    resultados.style.display = 'none';
  }

  function seleccionar(id, nombre) {
    hidden.value = id;
    input.value = nombre;
    input.dataset.confirmado = nombre;
    ocultarResultados();
  }

  async function buscar(termino) {
    if (controladorActual) controladorActual.abort();
    controladorActual = new AbortController();

    try {
      const respuesta = await fetch(config.url + '?q=' + encodeURIComponent(termino), {
        signal: controladorActual.signal,
      });
      const alumnos = await respuesta.json();

      resultados.innerHTML = '';

      if (alumnos.length === 0) {
        const vacio = document.createElement('div');
        vacio.className = 'buscador-alumno-vacio';
        vacio.textContent = 'Sin coincidencias.';
        resultados.appendChild(vacio);
      }

      alumnos.forEach((alumno) => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'buscador-alumno-item';
        item.textContent = alumno.nombre;
        item.addEventListener('click', () => seleccionar(alumno.id, alumno.nombre));
        resultados.appendChild(item);
      });

      resultados.style.display = 'block';
    } catch (error) {
      // petición cancelada por una búsqueda más nueva; no hacer nada
    }
  }

  input.addEventListener('input', () => {
    hidden.value = '';
    clearTimeout(temporizadorBusqueda);

    const termino = input.value.trim();
    if (termino.length === 0) {
      ocultarResultados();
      return;
    }

    temporizadorBusqueda = setTimeout(() => buscar(termino), 250);
  });

  input.addEventListener('focus', () => {
    if (input.value.trim().length > 0 && resultados.children.length > 0) {
      resultados.style.display = 'block';
    }
  });

  document.addEventListener('click', (evento) => {
    if (!resultados.contains(evento.target) && evento.target !== input) {
      ocultarResultados();
    }
  });
};
