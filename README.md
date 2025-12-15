ñ# 🤖 Bot de Práctica con WebSockets (PHP)

Un proyecto simple para aprender y practicar WebSockets usando PHP y HTML/JavaScript.

## 📋 Características

- ✅ Servidor WebSocket con PHP (Ratchet)
- ✅ Cliente HTML con interfaz moderna
- ✅ Bot con respuestas inteligentes
- ✅ Reconexión automática
- ✅ Indicador de escritura
- ✅ Animaciones suaves
- ✅ Diseño responsive

## 🚀 Instalación

**¡No requiere instalación de dependencias!** 

Este bot usa sockets nativos de PHP, no necesitas instalar Composer ni ninguna librería externa.

## 📦 Uso

1. **Iniciar el servidor WebSocket:**
   ```bash
   php server.php
   ```
   
   El servidor se iniciará en `ws://localhost:8080`

2. **Abrir el cliente:**
   - Abre el archivo `index.html` en tu navegador
   - O accede a través de tu servidor Apache: `http://localhost/bot/`

## 🎯 Cómo funciona

### Servidor (server.php)
- Usa sockets nativos de PHP (sin dependencias externas)
- Crea un servidor WebSocket en el puerto 8080
- Escucha mensajes de los clientes
- Analiza el contenido del mensaje con expresiones regulares
- Responde con mensajes apropiados según palabras clave

### Cliente (index.html)
- Se conecta al servidor WebSocket
- Envía mensajes del usuario
- Muestra las respuestas del bot
- Maneja reconexiones automáticas

## 💬 Prueba estos comandos

Escribe en el chat:
- "Hola" - El bot te saludará
- "¿Cómo estás?" - Te dirá cómo se siente
- "¿Qué hora es?" - Te dirá la hora actual
- "Websockets" - Te hablará sobre WebSockets
- "PHP" - Te hablará sobre PHP
- "Gracias" - Te responderá amablemente
- "Adiós" - Se despedirá de ti

## 🛠️ Tecnologías

- **Backend:** PHP 7.4+ (sockets nativos, sin dependencias)
- **Frontend:** HTML5 + CSS3 + JavaScript Vanilla
- **Protocolo:** WebSocket (ws://)

## 📝 Notas

- El servidor debe estar corriendo para que el chat funcione
- Si cambias el puerto, actualiza también la URL en `index.html`
- El bot tiene un delay aleatorio de 500-1500ms para simular "pensamiento"
- Asegúrate de que el puerto 8080 no esté siendo usado por otro servicio

## 🎨 Personalización

Puedes personalizar:
- Las respuestas del bot en `server.php` (array `$botResponses`)
- Los colores y estilos en `index.html` (sección `<style>`)
- El puerto del servidor en `server.php` (última línea)

## 📚 Aprendizaje

Este proyecto es ideal para:
- Entender la comunicación bidireccional en tiempo real
- Aprender el protocolo WebSocket
- Practicar PHP orientado a objetos
- Ver cómo funciona un chat bot simple
- Aprender a trabajar con sockets nativos en PHP

## 🐘 Requisitos

- PHP 7.4 o superior
- Extensión PHP: sockets (generalmente viene habilitada por defecto)

¡Diviértete practicando! 🚀
