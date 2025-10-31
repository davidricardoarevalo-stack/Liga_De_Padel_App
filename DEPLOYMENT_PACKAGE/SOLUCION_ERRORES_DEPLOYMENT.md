# 🚨 SOLUCIÓN A ERRORES DE DEPLOYMENT

## Problema Identificado

**Error 1**: "Not found" al subir carpeta `bootstrap`
**Error 2**: `404 - https://ligadepadeldebogotaoficial.com/LPBApp/api/clubs`

## 🔍 Diagnóstico

El problema está en la estructura de directorios y configuración de rutas. La aplicación está buscando la API en una ruta que no existe o no está correctamente configurada.

## ✅ SOLUCIÓN PASO A PASO

### 1. **Verificar Estructura en el Servidor**

Primero, confirma que en Bluehost tengas esta estructura:

```
public_html/
├── LPBApp/                          # Carpeta de la aplicación
│   ├── index.html                   # Frontend React
│   ├── static/                      # Archivos CSS/JS
│   ├── images/                      # Logo y recursos
│   └── api/                         # Backend PHP
│       ├── app/
│       ├── bootstrap/               # ← Esta carpeta es CRÍTICA
│       ├── public/
│       │   ├── index.php            # Punto de entrada de la API
│       │   └── .htaccess
│       ├── routes/
│       ├── database/
│       └── composer.json
```

### 2. **Verificar Contenido de bootstrap/app.php**

El archivo `api/bootstrap/app.php` es FUNDAMENTAL. Debe contener:

```php
<?php
// Este archivo NO debe modificarse - es el core de la aplicación
require_once __DIR__ . '/../vendor/autoload.php';
// ... resto del código
```

### 3. **Verificar .htaccess en api/public/**

Debe tener:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### 4. **SOLUCIÓN INMEDIATA - Opción A: Reconfigurar Frontend**

Cambia la configuración para apuntar directamente a donde está funcionando:

En el archivo de configuración del frontend, cambiar:

```javascript
// En lugar de:
backendUrl = '/LPBApp/api';

// Usar:
backendUrl = 'https://ligadepadeldebogotaoficial.com/LPBApp/api';
```

### 5. **SOLUCIÓN INMEDIATA - Opción B: Verificar Ruta de API**

Prueba acceder directamente a:
- `https://ligadepadeldebogotaoficial.com/LPBApp/api/`
- `https://ligadepadeldebogotaoficial.com/LPBApp/api/login`

Si estas URLs no funcionan, el problema está en el backend.

## 🛠️ PASOS DE REPARACIÓN

### Paso 1: Reinstalar Bootstrap Correctamente

1. **Elimina** la carpeta `api/bootstrap/` del servidor
2. **Sube nuevamente** la carpeta completa desde el paquete
3. **Verifica permisos**: 755 para carpetas, 644 para archivos

### Paso 2: Reinstalar Composer

```bash
# En terminal de cPanel o SSH:
cd /public_html/LPBApp/api/
rm -rf vendor/
composer install --no-dev --optimize-autoloader
```

### Paso 3: Verificar index.php

En `api/public/index.php` debe tener al inicio:

```php
<?php
require_once __DIR__ . '/../bootstrap/app.php';
```

### Paso 4: Probar la API

Accede a: `https://ligadepadeldebogotaoficial.com/LPBApp/api/`

Deberías ver algo como:
```json
{
  "status": "OK",
  "message": "Liga de Padel API - Development Environment"
}
```

## 🔧 ARCHIVO DE CONFIGURACIÓN TEMPORAL

Crea un archivo `test-api.php` en `/LPBApp/` con:

```php
<?php
// Test de conectividad API
header('Content-Type: application/json');

// Verificar si la API está accesible
$api_url = '/LPBApp/api/';
$full_url = 'https://' . $_SERVER['HTTP_HOST'] . $api_url;

echo json_encode([
    'status' => 'test',
    'api_url' => $api_url,
    'full_url' => $full_url,
    'server' => $_SERVER['HTTP_HOST'],
    'path' => $_SERVER['REQUEST_URI']
]);
?>
```

Accede a: `https://ligadepadeldebogotaoficial.com/LPBApp/test-api.php`

## 🚨 SOLUCIÓN RÁPIDA DE EMERGENCIA

Si necesitas que funcione INMEDIATAMENTE:

### 1. Cambiar configuración del frontend

Edita el archivo `/LPBApp/static/js/main.*.js` y busca:

```javascript
"/LPBApp/api"
```

Cámbialo por:

```javascript
"https://ligadepadeldebogotaoficial.com/LPBApp/api"
```

### 2. O crear un nuevo build con configuración correcta

En el proyecto local:

```bash
# En web-app/src/config.js cambiar:
backendUrl = 'https://ligadepadeldebogotaoficial.com/LPBApp/api';

# Hacer nuevo build:
npm run build

# Subir solo los archivos static/ nuevos
```

## 📞 VERIFICACIÓN FINAL

Después de aplicar la solución:

1. ✅ `https://ligadepadeldebogotaoficial.com/LPBApp/` - Frontend carga
2. ✅ `https://ligadepadeldebogotaoficial.com/LPBApp/api/` - API responde
3. ✅ Login funciona sin errores 404
4. ✅ Todas las funcionalidades operativas

---

**¿Cuál de estas soluciones prefieres probar primero?**