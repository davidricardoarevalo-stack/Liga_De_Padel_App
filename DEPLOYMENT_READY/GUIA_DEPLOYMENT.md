# 🚀 GUÍA FINAL DE DESPLIEGUE - BLUEHOST SIN DOCKER

## 📂 ARCHIVOS PREPARADOS
Todos los archivos están listos en: `DEPLOYMENT_READY/LPBApp/`

### ✅ **Lo que YA está configurado:**
- ✅ Frontend React con rutas `/LPBApp/`
- ✅ Backend PHP nativo (sin Docker)
- ✅ vendor/ con Firebase JWT
- ✅ .env con credenciales de producción
- ✅ .htaccess para redirecciones
- ✅ APP_KEY y JWT_SECRET seguros

## 🎯 PASOS PARA SUBIR A BLUEHOST

### **1. Subir carpeta completa**
Sube toda la carpeta `DEPLOYMENT_READY/LPBApp/` a `/public_html/LPBApp/`

### **2. Estructura final en servidor:**
```
/public_html/LPBApp/
├── index.html              ✅ (React)
├── static/                 ✅ (CSS/JS React)
├── manifest.json           ✅ (React)
├── .htaccess              ✅ (redirecciones)
└── api/                    ✅ (PHP nativo)
    ├── .env               ✅ (producción)
    ├── vendor/            ✅ (Firebase JWT)
    ├── public/index.php   ✅ (entrada API)
    ├── bootstrap/app.php  ✅ (sin Docker)
    ├── app/Controllers/   ✅ (lógica)
    └── routes/web.php     ✅ (rutas API)
```

### **3. Configurar permisos en Bluehost:**
```
chmod 755 /public_html/LPBApp/api/
chmod 644 /public_html/LPBApp/api/.env
chmod 644 /public_html/LPBApp/api/.htaccess
chmod 644 /public_html/LPBApp/api/public/index.php
chmod -R 755 /public_html/LPBApp/api/vendor/
```

### **4. URLs de verificación:**
1. **Frontend:** https://ligadepadeldebogotaoficial.com/LPBApp/
2. **API Test:** https://ligadepadeldebogotaoficial.com/LPBApp/api/test_api.php
3. **Dependencies:** https://ligadepadeldebogotaoficial.com/LPBApp/api/test_dependencies.php

### **5. Login de prueba:**
- Email: `app@app.com`
- Password: `123`

## 🔧 DIFERENCIAS DEV vs PRODUCCIÓN

| Aspecto | DEV (Docker) | PRODUCCIÓN (Bluehost) |
|---------|--------------|----------------------|
| **PHP** | Container | PHP 8.3 nativo |
| **Dependencias** | Volume mount | vendor/ físico |
| **Variables** | docker-compose.yml | .env archivo |
| **CORS** | localhost:3000 | ligadepadeldebogotaoficial.com |
| **Rutas** | /api/ | /LPBApp/api/ |
| **Bootstrap** | Lumen framework | PHP nativo simple |

## ⚠️ IMPORTANTE:
- **NO subir** archivos Docker (docker-compose.yml, Dockerfile)
- **SÍ subir** vendor/ completo con dependencias
- **Verificar** que .env no termine en .production
- **Comprobar** permisos 644 para archivos PHP

## 🎉 **¡LISTO PARA SUBIR!**
Todo está configurado para funcionar sin Docker en Bluehost.