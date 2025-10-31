# 🚀 Guía de Despliegue - Liga de Padel en Bluehost

## 📋 Requisitos Previos

### En Bluehost necesitas:
- ✅ Hosting con soporte PHP 7.4+ 
- ✅ MySQL Database
- ✅ Acceso a phpMyAdmin
- ✅ Acceso a File Manager o FTP

## 🔄 Proceso de Despliegue

### **1. Preparar Frontend (React)**
```bash
cd web-app
npm run build
```
- Esto genera la carpeta `build/` con archivos estáticos optimizados

### **2. Configurar Base de Datos MySQL**

#### 2.1 Crear Base de Datos en Bluehost:
1. Ir a **cPanel > MySQL Databases**
2. Crear nueva base de datos: `tu_usuario_liga_padel`
3. Crear usuario MySQL con permisos completos
4. Anotar: nombre de BD, usuario y contraseña

#### 2.2 Importar Estructura:
1. Abrir **phpMyAdmin**
2. Seleccionar tu base de datos
3. Ir a pestaña **"Import"**
4. Subir archivo `database_production.sql`
5. Ejecutar import

### **3. Configurar Backend (PHP)**

#### 3.1 Editar configuración:
Editar archivo `api/.env.production`:
```env
DB_HOST=localhost
DB_DATABASE=tu_usuario_liga_padel  
DB_USERNAME=tu_usuario_mysql
DB_PASSWORD=tu_password_mysql
APP_URL=https://tu-dominio.com
```

#### 3.2 Generar nueva APP_KEY:
```bash
# Generar nueva clave de 32 caracteres
# Usar herramientas online o: php artisan key:generate
APP_KEY=base64:TU_NUEVA_CLAVE_SEGURA_AQUI
```

### **4. Subir Archivos a Bluehost**

#### Estructura final en public_html/LPBApp/:
```
public_html/LPBApp/
├── index.html              (React build con PUBLIC_URL=/LPBApp)
├── static/                 (CSS/JS de React optimizados)
├── manifest.json           (React PWA manifest)
├── .htaccess              (redirecciones principales /LPBApp/)
└── api/                   (Backend PHP sin Docker)
    ├── .env               (configuración producción)
    ├── .htaccess          (redirecciones API)
    ├── vendor/            (Firebase JWT + dependencias)
    ├── public/
    │   └── index.php      (punto entrada API)
    ├── bootstrap/
    │   └── app.php        (bootstrap PHP nativo)
    ├── app/
    │   └── Http/Controllers/
    └── routes/
        └── web.php
```

#### 4.1 Subir archivos preparados:

**✅ ARCHIVOS LISTOS:** Carpeta `DEPLOYMENT_READY/LPBApp/` contiene todo lo necesario

1. Acceder a **cPanel > File Manager**
2. Ir a `public_html/`
3. **Subir toda la carpeta `LPBApp/`** (completa, no solo contenido)
4. La estructura quedará: `/public_html/LPBApp/`

#### 4.2 Configuración incluida:
- ✅ React build con rutas `/LPBApp/`
- ✅ API PHP sin Docker con vendor/
- ✅ .env con credenciales de producción
- ✅ .htaccess para redirecciones correctas
- ✅ Firebase JWT y dependencias

### **5. Configurar Permisos**
```bash
# Permisos para archivos y carpetas del API
chmod 755 /public_html/LPBApp/api/
chmod 644 /public_html/LPBApp/api/.env
chmod 644 /public_html/LPBApp/api/.htaccess
chmod 644 /public_html/LPBApp/api/public/index.php
chmod -R 755 /public_html/LPBApp/api/vendor/
```

### **6. URLs y Redirecciones (YA CONFIGURADAS)**

Las redirecciones ya están configuradas en los .htaccess incluidos:

#### 6.1 .htaccess principal (/public_html/LPBApp/.htaccess):
```apache
# API Routes para /LPBApp/api/*
RewriteCond %{REQUEST_URI} ^/LPBApp/api/
RewriteRule ^api/(.*)$ api/public/index.php [QSA,L]

# React SPA routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /LPBApp/index.html [L]
```

### **7. Verificar Funcionamiento**

#### 7.1 Probar Frontend:
```
https://ligadepadeldebogotaoficial.com/LPBApp/
```
Debería cargar la aplicación React

#### 7.2 Probar API:
```
https://ligadepadeldebogotaoficial.com/LPBApp/api/test_api.php
```
Debería retornar: `{"status": "API funcionando", "php_version": "8.3.x"}`

#### 7.3 Probar Login:

**⚠️ IMPORTANTE:** El endpoint `/api/login` requiere método POST, no GET

**Opción 1 - Interface de prueba (recomendado):**
```
https://ligadepadeldebogotaoficial.com/LPBApp/api/test_interface.html
```
Interface web para probar todos los endpoints

**Opción 2 - Test directo:**
```
https://ligadepadeldebogotaoficial.com/LPBApp/api/test_login.php
```
Endpoint POST directo para login

**Opción 3 - Usando curl:**
```bash
curl -X POST https://ligadepadeldebogotaoficial.com/LPBApp/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"app@app.com","password":"123"}'
```

**Credenciales de prueba:**
- Email: `app@app.com`
- Password: `123`

## 🛠️ Solución de Problemas Comunes

### Error 500 - Internal Server Error:
- ✅ Verificar permisos de carpetas
- ✅ Revisar logs de error en cPanel
- ✅ Verificar configuración .env

### Frontend no carga:
- ✅ Verificar que index.html esté en raíz
- ✅ Revisar .htaccess para SPA
- ✅ Verificar rutas de archivos estáticos

### API no responde:
- ✅ Verificar ruta `/api/` en .htaccess
- ✅ Comprobar conexión a base de datos
- ✅ Verificar vendor/ existe (dependencias PHP)

### Base de datos no conecta:
- ✅ Verificar credenciales en .env
- ✅ Verificar permisos de usuario MySQL
- ✅ Comprobar nombre exacto de base de datos

## 📱 URLs de Producción

- **Frontend**: `https://ligadepadeldebogotaoficial.com/LPBApp/`
- **API Base**: `https://ligadepadeldebogotaoficial.com/LPBApp/api/`
- **Test Interface**: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_interface.html`
- **API Test**: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_api.php`
- **Dependencies Test**: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_dependencies.php`
- **Login Test**: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_login.php`
- **Gestión BD**: `https://ligadepadeldebogotaoficial.com:2083` (phpMyAdmin)

## 🔒 Seguridad Post-Despliegue

1. **Cambiar contraseña admin**:
   - Login con app@app.com / 123
   - Cambiar a contraseña segura

2. **Configurar HTTPS**:
   - Activar SSL en Bluehost
   - Forzar redirección HTTPS

3. **Backup automático**:
   - Configurar backups en cPanel
   - Backup de base de datos regular

## 📞 Soporte

Si tienes problemas:
1. Revisar logs de error en cPanel
2. Verificar configuración paso a paso
3. Probar componentes por separado (API, Frontend, BD)

¡Tu aplicación estará lista para producción! 🎉