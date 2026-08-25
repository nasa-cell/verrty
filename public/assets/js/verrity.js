/**
 * ORDENIA IA — Verrity (panel del profesor).
 * Controla el chat con la IA, el botón de generar ejercicios y la voz.
 *
 * La voz principal es una voz neuronal gratuita y descargada localmente
 * (Piper TTS + modelo en español "es_AR-daniela-high", femenina y de
 * tono vivo), generada por el servidor en GET /voz?texto=... Para que
 * suene más animada/juguetona, el navegador la reproduce con un
 * playbackRate levemente mayor a 1 (sube el tono un poco, como una
 * voz de personaje de dibujos animados) sin llegar a distorsionarla.
 *
 * Si el servidor no puede generarla (por ejemplo, en un hosting que no
 * permite ejecutar procesos, como InfinityFree), se usa
 * automáticamente la voz del navegador (Web Speech API /
 * SpeechSynthesis) como respaldo — nunca hay una dependencia
 * obligatoria de pago.
 */
(function () {
  'use strict';

  const cuerpo = document.querySelector('.verrity-cuerpo');
  const burbuja = document.querySelector('.verrity-burbuja');
  const log = document.querySelector('.verrity-log');
  const form = document.getElementById('form-pregunta-verrity');
  const input = document.getElementById('input-pregunta-verrity');
  const btnGenerar = document.getElementById('btn-generar-ejercicios');
  const btnPreguntarAlumno = document.getElementById('btn-preguntar-alumno');
  const selectAlumno = document.getElementById('select-alumno'); // input hidden con el id del alumno elegido
  const buscarAlumnoInput = document.getElementById('buscar-alumno'); // texto visible con el nombre elegido
  const selectTipoPregunta = document.getElementById('select-tipo-pregunta');
  const btnVoz = document.getElementById('btn-toggle-voz');
  const selectVoz = document.getElementById('select-voz');

  if (!cuerpo) return; // widget no presente en esta vista

  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  let vozActiva = localStorage.getItem('ordenia_voz_activa') !== 'false';
  actualizarBotonVoz();

  function estado(nombre) {
    cuerpo.className = 'verrity-cuerpo ' + nombre;
  }

  let audioVerrity = null;
  // Sube el tono levemente (efecto "voz animada/personaje"), sin perder
  // inteligibilidad. 1.0 = tono original de la voz descargada.
  const TONO_ANIMADO = 1.12;

  // Si la voz descargada (Piper) tarda más que esto en responder (por
  // ejemplo, un arranque en frío en el servidor), se cancela la espera
  // y se pasa de inmediato a la voz del navegador, para que Verrity
  // nunca se quede "congelada" en silencio esperando el audio.
  const TIEMPO_MAXIMO_ESPERA_VOZ_MS = 6000;

  /**
   * Habla el texto completo y NO se resuelve hasta que Verrity termina
   * de decirlo entero (o falla). Es importante esperar esta promesa
   * antes de hacer cosas como recargar la página: si se recarga
   * mientras todavía está hablando, la voz se corta a mitad de frase
   * y parece "atorada" — ese era el bug real.
   */
  async function decir(texto) {
    burbuja.textContent = texto;
    estado('pensando'); // hasta que el audio realmente empiece a sonar

    if (!vozActiva) {
      estado('');
      return;
    }

    const sonoDesdeElServidor = await hablarConVozDescargada(texto);

    if (!sonoDesdeElServidor) {
      await hablarConVozDelNavegador(texto);
    }
  }

  /** Voz neuronal descargada (Piper TTS). Se resuelve cuando termina de sonar; true si sonó completa. */
  function hablarConVozDescargada(texto) {
    return new Promise((resolve) => {
      const controlador = new AbortController();
      const limite = setTimeout(() => controlador.abort(), TIEMPO_MAXIMO_ESPERA_VOZ_MS);

      fetch('/voz?texto=' + encodeURIComponent(texto), { signal: controlador.signal })
        .then((respuesta) => {
          clearTimeout(limite);

          if (respuesta.status !== 200) {
            resolve(false); // el servidor no tiene la voz descargada disponible (ver VoiceService)
            return;
          }

          return respuesta.blob().then((audioBlob) => {
            if (audioBlob.size === 0) {
              resolve(false);
              return;
            }

            if (audioVerrity) audioVerrity.pause();
            audioVerrity = new Audio(URL.createObjectURL(audioBlob));
            audioVerrity.playbackRate = TONO_ANIMADO;
            audioVerrity.addEventListener('ended', () => {
              estado('');
              resolve(true);
            });
            audioVerrity.addEventListener('error', () => {
              estado('');
              resolve(false);
            });

            estado('hablando');
            audioVerrity.play().catch(() => resolve(false));
          });
        })
        .catch(() => {
          clearTimeout(limite);
          resolve(false); // abortado por timeout, red caída, etc. -> cae a la voz del navegador
        });
    });
  }

  // Referencia al vigilante anti-atasco de Chrome (ver más abajo).
  let vigilanteVozNavegador = null;

  /**
   * Respaldo 100% gratuito integrado en el navegador: Web Speech API.
   *
   * Chrome/Edge tienen un bug conocido: una sola frase larga (más de
   * ~15 segundos de audio) hace que la síntesis se quede "atorada" a
   * mitad de camino y nunca termine de leer el texto. Se evita de dos
   * formas: (1) partiendo el texto en oraciones cortas y encolándolas
   * como utterances independientes (la cola nativa las reproduce en
   * orden sin cortes perceptibles) y (2) un "vigilante" que llama a
   * pause()/resume() cada pocos segundos mientras se habla, el
   * workaround estándar para ese bug.
   */
  function hablarConVozDelNavegador(texto) {
    return new Promise((resolve) => {
      if (!('speechSynthesis' in window)) {
        estado('');
        resolve(false);
        return;
      }

      window.speechSynthesis.cancel();
      if (vigilanteVozNavegador) clearInterval(vigilanteVozNavegador);

      const oraciones = texto
        .split(/(?<=[.!?…])\s+/)
        .map((s) => s.trim())
        .filter((s) => s.length > 0);

      if (oraciones.length === 0) {
        estado('');
        resolve(false);
        return;
      }

      const vozElegida = obtenerVozSeleccionada();
      let terminado = false;

      // Red de seguridad: si por alguna falla del navegador "onend"
      // nunca llega, no dejar la promesa (ni un posible reload) colgada
      // para siempre. Tiempo generoso basado en la longitud del texto.
      const limiteSeguridad = setTimeout(() => {
        if (terminado) return;
        terminado = true;
        if (vigilanteVozNavegador) clearInterval(vigilanteVozNavegador);
        estado('');
        resolve(true);
      }, Math.min(45000, Math.max(4000, texto.length * 150)));

      oraciones.forEach((oracion, indice) => {
        const utterancia = new SpeechSynthesisUtterance(oracion);
        utterancia.lang = 'es-ES';
        if (vozElegida) utterancia.voice = vozElegida;

        if (indice === 0) {
          utterancia.onstart = () => estado('hablando');
        }

        if (indice === oraciones.length - 1) {
          utterancia.onend = () => {
            if (terminado) return;
            terminado = true;
            clearTimeout(limiteSeguridad);
            if (vigilanteVozNavegador) clearInterval(vigilanteVozNavegador);
            estado('');
            resolve(true);
          };
        }

        window.speechSynthesis.speak(utterancia);
      });

      // Vigilante anti-atasco: mientras haya algo pendiente de leer,
      // "despertar" la síntesis cada 5s (workaround del bug de Chrome).
      vigilanteVozNavegador = setInterval(() => {
        if (!window.speechSynthesis.speaking) {
          clearInterval(vigilanteVozNavegador);
          return;
        }
        window.speechSynthesis.pause();
        window.speechSynthesis.resume();
      }, 5000);
    });
  }

  // Mismas frases que VerrityPhraseBank::FRASES['choque_manos'] en el
  // backend (ver app/Support/VerrityPhraseBank.php) — duplicadas aquí
  // porque es solo un gesto decorativo del cliente, no un dato real.
  const FRASES_CHOQUE_MANOS = ['¡Chócala! 🙌', '¡Choca esos cinco! ✋', '¡Eso es trabajo en equipo! 🙌', '¡Buen trabajo, profesor! 🙌'];

  /** Pequeño gesto de celebración al terminar una tarea con éxito (generar ejercicios). */
  function saludarConChoqueDeManos() {
    return new Promise((resolve) => {
      burbuja.textContent = FRASES_CHOQUE_MANOS[Math.floor(Math.random() * FRASES_CHOQUE_MANOS.length)];
      estado('saludando');
      setTimeout(() => {
        estado('');
        resolve();
      }, 700);
    });
  }

  function agregarAlLog(clase, texto) {
    if (!log) return;
    const div = document.createElement('div');
    div.className = clase;
    div.textContent = texto;
    log.appendChild(div);
    log.scrollTop = log.scrollHeight;
  }

  // Lista ordenada de voces mostrada en el selector (español primero).
  // Se guarda aparte de speechSynthesis.getVoices() porque su orden
  // puede variar entre llamadas en algunos navegadores.
  let vocesOrdenadas = [];

  function cargarVoces() {
    if (!('speechSynthesis' in window) || !selectVoz) return;

    const voces = window.speechSynthesis.getVoices();
    vocesOrdenadas = [...voces].sort((a, b) => {
      const aEsEspanol = a.lang.toLowerCase().startsWith('es');
      const bEsEspanol = b.lang.toLowerCase().startsWith('es');
      if (aEsEspanol === bEsEspanol) return a.name.localeCompare(b.name);
      return aEsEspanol ? -1 : 1;
    });

    selectVoz.innerHTML = '';
    vocesOrdenadas.forEach((voz, indice) => {
      const opcion = document.createElement('option');
      opcion.value = indice;
      opcion.textContent = `${voz.name} (${voz.lang})`;
      selectVoz.appendChild(opcion);
    });
  }

  function obtenerVozSeleccionada() {
    if (!selectVoz || !('speechSynthesis' in window)) return null;
    const indice = parseInt(selectVoz.value, 10);
    return Number.isInteger(indice) ? vocesOrdenadas[indice] : null;
  }

  if ('speechSynthesis' in window) {
    cargarVoces();
    window.speechSynthesis.onvoiceschanged = cargarVoces;
  }

  function actualizarBotonVoz() {
    if (!btnVoz) return;
    btnVoz.textContent = vozActiva ? '🔊 Voz activada' : '🔇 Voz desactivada';
  }

  if (btnVoz) {
    btnVoz.addEventListener('click', () => {
      vozActiva = !vozActiva;
      localStorage.setItem('ordenia_voz_activa', vozActiva);
      actualizarBotonVoz();
      if (!vozActiva && 'speechSynthesis' in window) window.speechSynthesis.cancel();
    });
  }

  if (form) {
    form.addEventListener('submit', async (evento) => {
      evento.preventDefault();
      const pregunta = input.value.trim();
      if (!pregunta) return;

      agregarAlLog('mensaje-profesor', pregunta);
      input.value = '';
      estado('pensando');
      burbuja.textContent = 'Déjame revisar el rendimiento... 🧠';

      try {
        const respuesta = await fetch(form.action, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ pregunta }),
        });
        const datos = await respuesta.json();

        agregarAlLog('mensaje-verrity', datos.texto);
        decir(datos.texto);
      } catch (error) {
        estado('error');
        decir('Algo salió mal. Voy a intentarlo nuevamente.');
      }
    });
  }

  if (btnPreguntarAlumno) {
    btnPreguntarAlumno.addEventListener('click', async () => {
      const alumnoId = selectAlumno.value;
      const tipo = selectTipoPregunta.value;

      if (!alumnoId) {
        decir('Seleccione primero un alumno.');
        return;
      }

      const etiquetaPregunta = selectTipoPregunta.selectedOptions[0].textContent;
      const nombreAlumno = buscarAlumnoInput.value;
      agregarAlLog('mensaje-profesor', `${etiquetaPregunta} (${nombreAlumno})`);

      btnPreguntarAlumno.classList.add('btn--loading');
      estado('pensando');
      burbuja.textContent = 'Déjame revisar el rendimiento... 🧠';

      try {
        const respuesta = await fetch(btnPreguntarAlumno.dataset.url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ tipo, alumno_id: alumnoId }),
        });
        const datos = await respuesta.json();

        if (datos.error) {
          estado('error');
          await decir(datos.error);
        } else {
          agregarAlLog('mensaje-verrity', datos.texto);
          await decir(datos.texto);
        }
      } catch (error) {
        estado('error');
        await decir('Algo salió mal. Voy a intentarlo nuevamente.');
      } finally {
        btnPreguntarAlumno.classList.remove('btn--loading');
      }
    });
  }

  if (btnGenerar) {
    btnGenerar.addEventListener('click', async () => {
      const alumnoId = selectAlumno.value;
      if (!alumnoId) {
        decir('Seleccione primero un alumno.');
        return;
      }

      btnGenerar.classList.add('btn--loading');
      estado('generando');
      burbuja.textContent = 'Generando ejercicios...';

      try {
        const respuesta = await fetch(btnGenerar.dataset.url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ alumno_id: alumnoId, cantidad: 10 }),
        });
        const datos = await respuesta.json();

        if (datos.error) {
          estado('error');
          await decir(datos.error);
          btnGenerar.classList.remove('btn--loading');
        } else {
          estado('celebrando');
          agregarAlLog('mensaje-verrity', datos.mensaje);
          // Importante: esperar a que Verrity termine de hablar ANTES
          // de recargar. Recargar antes cortaba la voz a mitad de
          // frase (el bug de "no lee completo").
          await decir(datos.mensaje);
          await saludarConChoqueDeManos();
          window.location.reload();
        }
      } catch (error) {
        estado('error');
        await decir('No pude generar el ejercicio. Revisaré la configuración.');
        btnGenerar.classList.remove('btn--loading');
      }
    });
  }

  // Animación de aparición al cargar el panel.
  estado('aparecer');
  setTimeout(() => estado(''), 500);

  // ----------------------------------------------------------------
  // Expresiones ocasionales: cada 8 segundos, mientras Verrity está
  // inactiva (nadie le preguntó nada ni se está generando algo), dice
  // una frase corta con un pequeño gesto (guiño o sonrisa) — para que
  // se sienta "viva". Se apaga si el usuario prefiere menos
  // movimiento, y se pausa si la pestaña no está visible.
  // ----------------------------------------------------------------
  const INTERVALO_FRASE_INACTIVA_MS = 8000;
  const prefiereMenosMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  // 100 frases escritas a mano, sincronizadas con
  // VerrityPhraseBank::FRASES['inactiva_base'] en el backend
  // (app/Support/VerrityPhraseBank.php es la referencia canónica; si
  // se agregan más ahí, deben copiarse también aquí). Se combinan más
  // abajo con 400 generadas por plantillas para llegar a 500 en total.
  const FRASES_INACTIVA_BASE = [
    '¿Todo bien por aquí? 👀',
    'Sigo atenta a los datos de sus alumnos...',
    '¿Alguna otra duda, profesor?',
    '🧠 Pensando en nuevas formas de ayudar...',
    '¡Aquí ando, lista para lo que necesite!',
    'Los números nunca duermen. 😄',
    'Puede preguntarme por cualquier alumno cuando quiera.',
    '¡Todo tranquilo por el momento!',
    'Mayor a menor, menor a mayor... ¡siempre hay que ordenar! 🔢',
    'Me encanta ver cómo mejoran sus alumnos. 📈',
    '¿Sabía que reviso el rendimiento en tiempo real?',
    'Estoy aquí, observando en silencio. 🔵',
    'A veces me pregunto qué número seguirá... 🤔',
    '¡Qué bueno tenerlo por aquí, profesor!',
    'Ordenar números es más divertido de lo que parece.',
    'Cada ejercicio cuenta una historia distinta.',
    'Si necesita un reporte, solo avíseme.',
    'Estoy lista para generar ejercicios cuando usted diga.',
    'Redondita, azul y siempre atenta. 🔵',
    '¿Ya revisó el ranking de sus alumnos hoy?',
    'Aprendo algo nuevo con cada alumno.',
    '¡Vamos a hacer de las matemáticas algo divertido!',
    'Un número más, un paso más hacia el aprendizaje.',
    'Me tomo muy en serio lo de ordenar de mayor a menor.',
    '¿Le gustaría generar ejercicios para algún alumno?',
    'Todo dato que ve viene directo de la base de datos.',
    'No invento nada, solo analizo lo real. 📊',
    'A veces el silencio también enseña. 🤫',
    'Estoy pensando en el próximo desafío para sus alumnos.',
    '¡Qué gusto acompañarlo en esta sesión!',
    'Cada clic suyo me mantiene ocupada. 😊',
    'Los patrones en los datos me fascinan.',
    '¿Cómo va el día, profesor?',
    'Sigo aquí, aunque no lo parezca.',
    'Un buen maestro siempre está atento, como yo. 👀',
    'Si algo no cuadra, aquí estoy para revisarlo.',
    'Ordenar es más que números: es lógica pura.',
    'Espero que sus alumnos estén progresando bien.',
    '¡La constancia es la clave del aprendizaje!',
    'Me alegra ser parte de este salón virtual.',
    '¿Ya pensó en su próximo reporte?',
    'Aquí sigo, lista para lo que decida.',
    'Cada alumno mejora a su propio ritmo. 🌱',
    '¡Los números también tienen personalidad! 🔢',
    'Estoy en modo espera, pero muy atenta.',
    'Ver progreso es lo que más me gusta.',
    '¿Alguna duda sobre el sistema, profesor?',
    'Sin prisa, pero sin pausa. 🐢',
    'Todo bien de mi lado, ¿y del suyo?',
    'Cada número ordenado es una pequeña victoria.',
    'Analizar rendimiento nunca fue tan sencillo.',
    'Estoy lista si necesita generar más ejercicios.',
    'La práctica hace al maestro... y al alumno. 📚',
    '¡Qué tranquilidad reina en el panel hoy!',
    'Sigo procesando información en segundo plano.',
    'Cada clic me recuerda que estoy aquí para ayudar.',
    'Ordenar de mayor a menor es mi especialidad. 🔵',
    '¿Necesita que revise algún caso en especial?',
    'Disfruto acompañando el proceso de aprendizaje.',
    'Todo tranquilo, esperando su próxima pregunta.',
    'Cada alumno es un mundo de números distintos.',
    '¡Los datos nunca mienten! 📈',
    'Estoy pensando en cómo mejorar mis respuestas.',
    '¿Le parece si revisamos el progreso general?',
    'Aquí sigo, redondita y atenta. 🔵',
    'La curiosidad me mantiene activa.',
    'Ordenar bien es pensar bien. 🧠',
    'Espero estar siendo de ayuda, profesor.',
    'Cada segundo cuenta en el aprendizaje.',
    'Sigo lista para lo que necesite a continuación.',
    '¡Vamos por más aciertos hoy!',
    'Analizar es mi pasatiempo favorito. 😄',
    '¿Todo en orden con sus alumnos?',
    'Un dato bien leído vale más que mil suposiciones.',
    'Sigo aquí, esperando su próxima instrucción.',
    'Cada vez aprendo a explicar mejor los números.',
    '¡Que tenga una excelente sesión, profesor!',
    'Estoy atenta a cualquier cambio en el rendimiento.',
    'La paciencia también es parte de enseñar. 🕊️',
    'Todo listo de mi lado para seguir ayudando.',
    'Los ejercicios nuevos siempre me emocionan un poco. 😊',
    '¿Ya decidió a qué alumno ayudar hoy?',
    'Sigo procesando en silencio, como siempre.',
    'Cada número tiene su lugar correcto. 🔢',
    'Estoy aquí, lista para la próxima consulta.',
    'Ordenar bien también es una forma de pensar claro.',
    '¡Gracias por confiar en mí, profesor!',
    'Sigo observando el progreso general del grupo.',
    'Todo marcha con calma por aquí.',
    'Cada alumno tiene su propio ritmo de aprendizaje. 🌱',
    'Estoy lista para lo que decida hacer ahora.',
    '¡Los números también cuentan historias! 📊',
    'Sigo aquí, esperando con una sonrisa. 😊',
    'La constancia construye grandes resultados.',
    'Todo en calma, lista para ayudar cuando guste.',
    'Cada pregunta suya me ayuda a ser más útil.',
    'Sigo pendiente de cualquier novedad.',
    '¡Que los números estén siempre a su favor!',
    'Estoy aquí, azul y redonda, lista para lo que sea. 🔵',
    'Gracias por darme un lugar en su salón de clases. 💙',
  ];

  // Mismos componentes que INACTIVA_APERTURA/TEMA/CIERRE en el backend
  // (VerrityPhraseBank.php). 10 aperturas × 10 temas × 4 cierres = 400
  // frases generadas, que sumadas a las 100 de arriba dan 500 en total
  // sin tener que escribir 500 líneas sueltas en el navegador.
  const INACTIVA_APERTURA = [
    'Sigo aquí',
    'Todo tranquilo por aquí',
    'Estoy atenta',
    'Un momento de calma',
    'Aquí ando',
    'Nada nuevo por ahora',
    'Sigo en modo espera',
    'Todo en orden',
    'Aquí sigo, redondita y azul',
    'Me tomo un respiro',
  ];

  const INACTIVA_TEMA = [
    'pensando en los números de sus alumnos',
    'lista para ordenar de mayor a menor',
    'revisando patrones en los datos',
    'esperando su próxima pregunta',
    'lista para generar nuevos ejercicios',
    'observando el progreso del grupo',
    'cuidando cada detalle del rendimiento',
    'aprendiendo de cada alumno',
    'atenta a cualquier cambio',
    'disfrutando de esta sesión',
  ];

  const INACTIVA_CIERRE = ['🔵', '😊', '🧠', ''];

  function generarFrasesInactivas() {
    const generadas = [];
    INACTIVA_APERTURA.forEach((apertura) => {
      INACTIVA_TEMA.forEach((tema) => {
        INACTIVA_CIERRE.forEach((cierre) => {
          generadas.push(`${apertura}, ${tema}.${cierre ? ' ' + cierre : ''}`);
        });
      });
    });
    return generadas;
  }

  const FRASES_INACTIVA = FRASES_INACTIVA_BASE.concat(generarFrasesInactivas());

  /**
   * "Mazo" de frases: en vez de elegir una al azar cada vez (lo que con
   * pocas repeticiones ya puede repetir la misma frase por pura suerte),
   * se baraja un mazo con las 500 frases y se van sacando una por una
   * sin reponer. Cuando el mazo se vacía (ya salieron las 500), se
   * vuelve a barajar un mazo nuevo y se empieza otra vuelta desde cero.
   * Se guarda en localStorage para que, si el profesor cierra la
   * pestaña y vuelve más tarde, la vuelta siga donde iba en vez de
   * arrancar de nuevo (con riesgo de repetir lo último que ya dijo).
   */
  const CLAVE_MAZO_FRASES = 'ordenia_mazo_frases_v' + FRASES_INACTIVA.length;

  function nuevoMazoBarajado() {
    const indices = Array.from({ length: FRASES_INACTIVA.length }, (_, i) => i);
    for (let i = indices.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [indices[i], indices[j]] = [indices[j], indices[i]];
    }
    return indices;
  }

  function cargarMazoFrases() {
    try {
      const guardado = JSON.parse(localStorage.getItem(CLAVE_MAZO_FRASES));
      if (Array.isArray(guardado) && guardado.length > 0) return guardado;
    } catch (error) {
      // localStorage bloqueado (modo privado, etc.) o dato corrupto: se ignora.
    }
    return nuevoMazoBarajado();
  }

  function guardarMazoFrases(mazo) {
    try {
      localStorage.setItem(CLAVE_MAZO_FRASES, JSON.stringify(mazo));
    } catch (error) {
      // Sin localStorage disponible simplemente no persiste entre
      // recargas, pero la vuelta actual sigue sin repetir frases.
    }
  }

  let mazoFrasesInactiva = cargarMazoFrases();

  function proximaFraseInactiva() {
    if (mazoFrasesInactiva.length === 0) {
      mazoFrasesInactiva = nuevoMazoBarajado(); // se completó la vuelta de 500: empieza una nueva
    }

    const indice = mazoFrasesInactiva.pop();
    guardarMazoFrases(mazoFrasesInactiva);
    return FRASES_INACTIVA[indice];
  }

  if (!prefiereMenosMovimiento) {
    setInterval(() => {
      const estaInactiva = cuerpo.className.trim() === 'verrity-cuerpo';
      if (!estaInactiva || document.visibilityState !== 'visible') return;

      // decir() habla la frase de verdad (voz descargada o del
      // navegador) y además la muestra en la burbuja; no es solo un
      // cambio de texto silencioso.
      decir(proximaFraseInactiva());
    }, INTERVALO_FRASE_INACTIVA_MS);
  }
})();
