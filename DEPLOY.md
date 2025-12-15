# Bot WebSocket - Configuración para Producción

Este directorio contiene el servidor WebSocket PHP para desplegar en Railway.

## Archivos necesarios
- `server.php` - Servidor WebSocket
- `nixpacks.toml` - Configuración de build para Railway
- `railway.json` - Configuración de Railway
- `Procfile` - Comando de inicio

## Despliegue en Railway

### 1. Crear repositorio Git
```bash
cd c:\AppServ\www\bot
git init
git add .
git commit -m "Initial commit - WebSocket server"
```

### 2. Subir a GitHub
```bash
# Crea un nuevo repositorio en GitHub llamado "websocket-bot-server"
git remote add origin https://github.com/TU_USUARIO/websocket-bot-server.git
git branch -M main
git push -u origin main
```

### 3. Desplegar en Railway
1. Ve a https://railway.app
2. Inicia sesión con GitHub
3. Click en "New Project"
4. Selecciona "Deploy from GitHub repo"
5. Elige tu repositorio `websocket-bot-server`
6. Railway detectará automáticamente PHP y lo desplegará

### 4. Configurar el puerto
Railway te dará una URL como: `https://tu-proyecto.up.railway.app`
El WebSocket estará en: `wss://tu-proyecto.up.railway.app`

### 5. Actualizar tu frontend en Vercel
Agrega variable de entorno en Vercel:
- Nombre: `VITE_WEBSOCKET_URL`
- Valor: `wss://tu-proyecto.up.railway.app`

## Variables de entorno
Railway asigna automáticamente el puerto mediante la variable `PORT`.
El servidor ya está configurado para usar el puerto 8080 por defecto.

## Logs
Para ver los logs en Railway:
1. Ve a tu proyecto
2. Click en "Deployments"
3. Click en el deployment activo
4. Ve a "View Logs"
