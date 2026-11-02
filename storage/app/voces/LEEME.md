# Voz de Verrity — modelo de voz en español

`es_AR-daniela-high.onnx` + `es_AR-daniela-high.onnx.json` son un
modelo de voz neuronal en español (femenina, argentina, calidad alta,
tono vivo — elegida para que Verrity suene más animada), gratuito y de
uso libre, del catálogo oficial de voces de Piper TTS:
https://huggingface.co/rhasspy/piper-voices

Si esta carpeta falta (por ejemplo, porque se excluyó del ZIP por
peso — el modelo pesa ~110 MB por ser calidad "high"), se puede volver
a descargar así:

```
https://huggingface.co/rhasspy/piper-voices/resolve/main/es/es_AR/daniela/high/es_AR-daniela-high.onnx
https://huggingface.co/rhasspy/piper-voices/resolve/main/es/es_AR/daniela/high/es_AR-daniela-high.onnx.json
```

Ambos archivos van juntos en esta misma carpeta. Se puede cambiar por
cualquier otra voz en español del mismo catálogo (por ejemplo
`es_ES-davefx-medium`, masculina y ~60 MB más liviana, o
`es_ES-mls_10246-low` para una versión aún más liviana) actualizando la
variable `PIPER_MODEL` en el `.env` con la ruta al nuevo archivo
`.onnx`.
