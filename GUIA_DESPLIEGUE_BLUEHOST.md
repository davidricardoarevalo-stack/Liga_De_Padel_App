# 🚀 Guía de Despliegue - Liga de Padel en Bluehost

**✅ Aplicación:** Liga de Padel - Sistema de Gestión  
**🌐 Dominio:** ligadepadeldebogotaoficial.com/LPBApp  
**📅 Fecha:** Octubre 2025  
**⚡ Stack:** React + PHP/Lumen + MySQL

---

## 📋 Requisitos Previos

### En Bluehost necesitas:
- ✅ Hosting con soporte PHP 7.4+
- ✅ Base de datos MySQL
- ✅ Acceso a phpMyAdmin
- ✅ Acceso a File Manager o FTP
- ✅ Dominio configurado

---

## 🔄 Proceso de Despliegue Completo

### PASO 1: Preparar Frontend (React)

**Acción:** Generar build de producción

```bash
cd web-app
npm run build
```

Esto genera la carpeta `build/` con archivos estáticos optimizados para producción.

### PASO 2: Configurar Base de Datos MySQL

#### 2.1 Crear Base de Datos en Bluehost:
1. Ir a **cPanel > MySQL Databases**
2. Crear nueva base de datos: `ajkyinmy_liga_padel_app`
3. Crear usuario MySQL: `ajkyinmy_user_app`
4. Asignar permisos completos al usuario
5. Anotar credenciales para configuración

#### 2.2 Importar Estructura de Base de Datos:
1. Abrir **phpMyAdmin** en cPanel
2. Seleccionar la base de datos creada
3. Ir a pestaña **"Import"**
4. Subir archivo `database_production.sql`
5. Ejecutar importación

⚠️ **Importante:** Verificar que todas las tablas se crearon correctamente: users, clubs, athletes, tournaments

### PASO 3: Configurar Backend (PHP/Lumen)

#### 3.1 Configuración de Producción (.env):

El archivo `api/.env.production` debe contener:

```env
# Configuración Base de Datos
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ajkyinmy_liga_padel_app
DB_USERNAME=ajkyinmy_user_app
DB_PASSWORD=pwd_l1g4_app

# Configuración Aplicación
APP_NAME="Liga de Padel"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ligadepadeldebogotaoficial.com/LPBApp
APP_TIMEZONE=America/Bogota

# Seguridad
APP_KEY=base64:TU_CLAVE_SEGURA_AQUI

# CORS para producción
CORS_ALLOWED_ORIGINS=https://ligadepadeldebogotaoficial.com
```

#### 3.2 Generar APP_KEY Segura:
Usar generador online o comando PHP para crear clave de 32 caracteres.

### PASO 4: Estructura de Archivos en Servidor

#### Estructura final en public_html/LPBApp:

```
public_html/LPBApp/
├── index.html              (del build de React)
├── static/                 (CSS/JS de React)
│   ├── css/
│   └── js/
├── manifest.json          (del build de React)
├── favicon.ico
├── .htaccess              (redirecciones principales)
└── api/                   (Backend PHP)
    ├── app/
    ├── public/
    ├── vendor/            (dependencias PHP)
    ├── .env               (configuración producción)
    └── .htaccess          (configuración API)
```

#### 4.1 Subir archivos vía File Manager:
1. Acceder a **cPanel > File Manager**
2. Navegar a `public_html/LPBApp/`
3. Subir contenido de `web-app/build/` a la raíz de LPBApp
4. Crear carpeta `api/`
5. Subir contenido completo de `api/` a `public_html/LPBApp/api/`
6. Renombrar `.env.production` a `.env`

### PASO 5: Configurar Permisos de Archivos

Establecer permisos correctos en File Manager:

```bash
chmod 755 public_html/LPBApp/api/storage
chmod 755 public_html/LPBApp/api/bootstrap/cache
chmod 644 public_html/LPBApp/api/.env
chmod 644 public_html/LPBApp/.htaccess
chmod 644 public_html/LPBApp/api/.htaccess
```

### PASO 6: Configurar Redirecciones (.htaccess)

#### .htaccess principal (en LPBApp/):

```apache
Options -MultiViews -Indexes
RewriteEngine On

# API Routes
RewriteCond %{REQUEST_URI} ^/LPBApp/api
RewriteRule ^api/(.*)$ api/public/index.php [QSA,L]

# React App (SPA)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/LPBApp/api
RewriteRule . /LPBApp/index.html [L]
```

### PASO 7: Verificar Funcionamiento

#### 7.1 Probar API Backend:
```
https://ligadepadeldebogotaoficial.com/LPBApp/api/users
```
Debería retornar JSON con datos de usuarios

#### 7.2 Probar Frontend:
```
https://ligadepadeldebogotaoficial.com/LPBApp
```
Debería cargar la aplicación React completa

#### 7.3 Probar Login del Sistema:
- **Email:** app@app.com
- **Password:** 123

---

## 🛠️ Solución de Problemas Comunes

### Error 500 - Internal Server Error
- ✅ Verificar permisos de carpetas storage/ y cache/
- ✅ Revisar logs de error en cPanel > Error Logs
- ✅ Verificar configuración .env (credenciales DB)
- ✅ Comprobar que vendor/ existe (dependencias PHP)

### Frontend no carga
- ✅ Verificar que index.html esté en raíz de LPBApp/
- ✅ Revisar .htaccess para redirección SPA
- ✅ Verificar rutas de archivos estáticos (/static/)
- ✅ Comprobar configuración de URL base

### API no responde
- ✅ Verificar ruta /LPBApp/api/ en .htaccess
- ✅ Comprobar conexión a base de datos
- ✅ Verificar que public/index.php existe
- ✅ Revisar configuración CORS

### Base de datos no conecta
- ✅ Verificar credenciales exactas en .env
- ✅ Verificar permisos de usuario MySQL
- ✅ Comprobar nombre exacto de base de datos
- ✅ Probar conexión desde phpMyAdmin

---

## 📱 URLs de Producción Final

- **🌐 Frontend:** https://ligadepadeldebogotaoficial.com/LPBApp
- **🔌 API:** https://ligadepadeldebogotaoficial.com/LPBApp/api
- **🗄️ Base de Datos:** ajkyinmy_liga_padel_app
- **👤 Login Admin:** app@app.com / 123

---

## 🔒 Seguridad Post-Despliegue

### Tareas de Seguridad Obligatorias:

1. **Cambiar contraseña de administrador:**
   - Login con app@app.com / 123
   - Ir a gestión de usuarios
   - Cambiar a contraseña segura (8+ caracteres, mayúsculas, números, símbolos)

2. **Configurar HTTPS:**
   - Activar SSL en cPanel de Bluehost
   - Forzar redirección HTTPS en .htaccess

3. **Configurar backups automáticos:**
   - Activar backups en cPanel
   - Programar backup de base de datos semanal

4. **Generar APP_KEY segura:**
   - Reemplazar "GENERATE_NEW_KEY_FOR_PRODUCTION"
   - Usar clave de 32 caracteres única

---

## ✨ Características de la Aplicación

### Funcionalidades Implementadas:
- **Frontend:** Diseño completamente responsivo
- **Mobile:** Sin scroll horizontal en dispositivos móviles
- **UX:** Tablas optimizadas para todas las pantallas
- **Auth:** Sistema de autenticación JWT
- **CRUD:** Gestión completa de usuarios, deportistas, clubes, torneos
- **Filters:** Filtros inteligentes y búsquedas
- **Security:** Roles y permisos por usuario

### Roles de Usuario:
- **Administrador:** Acceso completo a todas las funciones
- **Asistente:** Gestión de deportistas y torneos
- **Club:** Gestión de sus propios deportistas
- **Deportista:** Vista de información personal

---

## 📞 Soporte y Mantenimiento

### En caso de problemas:
1. **Revisar logs de error:** cPanel > Error Logs
2. **Verificar configuración:** Seguir checklist paso a paso
3. **Probar componentes por separado:** API, Frontend, Base de datos
4. **Backup antes de cambios:** Siempre hacer respaldo

### Mantenimiento Recomendado:
- 📅 **Semanal:** Verificar logs de error
- 📅 **Mensual:** Backup completo de base de datos
- 📅 **Trimestral:** Actualizar dependencias de seguridad
- 📅 **Anual:** Renovar certificados SSL

---

## 🎉 ¡Despliegue Completado!

**Tu aplicación Liga de Padel está lista para producción en Bluehost con todas las mejoras responsivas y de seguridad implementadas.**

**URL:** `https://ligadepadeldebogotaoficial.com/LPBApp`

---

**Liga de Padel de Bogotá Oficial**  
Guía de Despliegue Técnico - Octubre 2025  
React + PHP/Lumen + MySQL en Bluehost