# Voz de Verrity — Piper TTS (motor)

Este binario es **Piper**, un motor de texto a voz neuronal gratuito y
de código abierto (licencia MIT): https://github.com/rhasspy/piper

Se descargó la build para Windows (`piper_windows_amd64.zip`, versión
`2023.11.14-2`) directamente desde los releases oficiales de GitHub.
Corre 100% en local: no envía nada a internet ni depende de ninguna
cuenta ni API de pago.

Si esta carpeta falta (por ejemplo, porque se excluyó del ZIP por
peso), se puede volver a descargar así:

```
https://github.com/rhasspy/piper/releases/download/2023.11.14-2/piper_windows_amd64.zip
```

Descomprimir y colocar `piper.exe` junto con los `.dll` directamente
en esta carpeta (`storage/piper/`).

**Compatibilidad:** esta es una build para Windows. En Linux/Mac se
debe descargar la build correspondiente desde la misma página de
releases. En hosting compartido gratuito (por ejemplo InfinityFree) no
se puede ejecutar ningún binario externo — en ese caso, `VoiceService`
detecta que Piper no está disponible y la aplicación usa
automáticamente la voz integrada del navegador (Web Speech API) sin
ningún cambio de código.
