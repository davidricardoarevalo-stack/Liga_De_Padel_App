# Guía de Despliegue - Producción Sin Docker

## Archivos a subir al servidor Bluehost:

### 1. **Carpeta vendor/ completa**
- **Origen:** `api/vendor/` (generada con Docker)
- **Destino:** `/public_html/LPBApp/api/vendor/`
- **Permisos:** 755 para carpetas, 644 para archivos

### 2. **Archivo .env**
- **Origen:** `api/.env.production`
- **Destino:** `/public_html/LPBApp/api/.env`
- **Permisos:** 644
- **Importante:** Renombrar de `.env.production` a `.env`

### 3. **Archivos de aplicación**
- **Origen:** `api/public/`, `api/bootstrap/`, `api/app/`, `api/routes/`
- **Destino:** `/public_html/LPBApp/api/`
- **Permisos:** 644 para archivos PHP, 755 para carpetas

### 4. **Archivos de configuración**
- **Origen:** `api/.htaccess`
- **Destino:** `/public_html/LPBApp/api/.htaccess`
- **Permisos:** 644

## Verificación post-despliegue:

1. **Verificar dependencias:**
   ```
   https://ligadepadeldebogotaoficial.com/LPBApp/api/production-bootstrap.php
   ```

2. **Verificar API básica:**
   ```
   https://ligadepadeldebogotaoficial.com/LPBApp/api/test_api.php
   ```

3. **Verificar configuración completa:**
   ```
   https://ligadepadeldebogotaoficial.com/LPBApp/api/test_dependencies.php
   ```

## Estructura final en servidor:
```
/public_html/LPBApp/
├── api/
│   ├── .env                    (configuración producción)
│   ├── .htaccess              (reglas Apache)
│   ├── vendor/                (dependencias PHP)
│   │   ├── autoload.php
│   │   ├── composer/
│   │   └── firebase/
│   ├── public/
│   │   └── index.php          (punto de entrada)
│   ├── bootstrap/
│   │   └── app.php            (bootstrap aplicación)
│   ├── app/
│   │   └── Http/Controllers/
│   └── routes/
│       └── web.php
├── static/                    (archivos React build)
└── index.html                 (React app)
```

## Características del entorno de producción:

✅ **Sin Docker:** Funciona directamente con PHP 8.3 nativo  
✅ **Autoload optimizado:** Composer autoload para cargar dependencias  
✅ **CORS dinámico:** Soporta desarrollo y producción  
✅ **JWT autónomo:** Firebase JWT sin dependencias adicionales  
✅ **Bootstrap simple:** Sin frameworks pesados como Lumen  
✅ **Variables de entorno:** Carga .env automáticamente