# 🚀 INSTRUCCIONES DE DEPLOYMENT - LIGA DE PADEL BOGOTÁ

## 📋 Contenido del Paquete

Este paquete contiene:
- **API Backend**: Carpeta `api/` con todos los archivos PHP actualizados
- **Frontend**: Carpeta `frontend/` con el build de producción de React
- **Documentación**: Este archivo con instrucciones

## ⚠️ IMPORTANTE - NO SOBRESCRIBIR

**NO subir estos archivos que ya están configurados en producción:**
- ❌ `.env` (mantener la configuración de Bluehost existente)
- ❌ `vendor/` (Composer dependencies ya instaladas)
- ❌ Archivos de base de datos existentes

## 🎯 Cambios Incluidos en esta Actualización

### 🎨 Mejoras de Diseño:
- ✅ Logo de Liga de Padel en login y banner
- ✅ Nueva paleta de colores corporativa:
  - Banner: #042653 (azul marino)
  - Encabezados: #092B58 (azul marino oscuro)
  - Botones principales: #D0DC30 (verde lima)
  - Botones cancelar: #901518 (rojo oscuro)

### ⚙️ Nuevas Funcionalidades:
- ✅ Botones "Cancelar" en todos los formularios
- ✅ Edición completa de torneos
- ✅ Validación de usuarios inactivos en login
- ✅ Diseño más limpio y profesional

## 📤 PASOS PARA SUBIR A BLUEHOST

### 1. **Preparación**
```
- Hacer backup completo del sitio actual
- Tener acceso a cPanel de Bluehost
- Confirmar que la base de datos está funcionando
```

### 2. **Subir Backend (API)**
```
1. Ir a cPanel → File Manager
2. Navegar a la carpeta /api/ en el servidor
3. HACER BACKUP de los archivos actuales
4. Subir SOLO estos archivos de la carpeta api/:

   📁 app/ (completa)
   📁 bootstrap/ (completa) 
   📁 database/ (completa)
   📁 public/ (completa)
   📁 routes/ (completa)
   📁 scripts/ (completa)
   📄 .htaccess
   📄 composer.json
   📄 production-bootstrap.php
   
   ⚠️ NO SUBIR: .env, vendor/, docker-compose.yml
```

### 3. **Actualizar Dependencias (si es necesario)**
```
1. Conectar por SSH o usar Terminal en cPanel
2. Navegar a la carpeta /api/
3. Ejecutar: composer install --no-dev --optimize-autoloader
```

### 4. **Subir Frontend**
```
1. En cPanel → File Manager
2. Navegar a la carpeta raíz del dominio (public_html)
3. HACER BACKUP de los archivos del frontend actual
4. Subir todos los archivos de la carpeta frontend/:
   
   📄 index.html
   📄 asset-manifest.json
   📄 favicon.ico
   📄 manifest.json
   📄 robots.txt
   📁 static/ (completa)
   📁 images/ (completa - incluye el nuevo logo)
```

### 5. **Verificar Funcionamiento**
```
1. Acceder a tu dominio
2. Probar login con credenciales existentes
3. Verificar que:
   ✅ Logo aparece en login y banner
   ✅ Colores nuevos se muestran correctamente
   ✅ Botones "Cancelar" funcionan
   ✅ Edición de torneos funciona
   ✅ Todas las funcionalidades existentes funcionan
```

### 6. **Configuración del Frontend (config.js)**
```
⚠️ CRÍTICO: Verificar que el archivo de configuración apunte a producción:

En /frontend/static/js/main.*.js buscar y confirmar que usa:
- URL de API de producción (no localhost:8080)
- Configuración correcta de CORS

Si es necesario, actualizar la configuración en React y hacer rebuild.
```

## 🔧 Resolución de Problemas

### Si el frontend no carga:
1. Verificar que todos los archivos static/ se subieron
2. Confirmar permisos de archivos (644 para archivos, 755 para carpetas)
3. Revisar .htaccess en la raíz

### Si el API no responde:
1. Verificar que .htaccess existe en /api/public/
2. Confirmar que composer install se ejecutó
3. Revisar logs de error de PHP en cPanel

### Si hay errores de CORS:
1. Verificar que el dominio de producción está en la lista de orígenes permitidos
2. Confirmar configuración en /api/public/index.php

## 📱 URLs Finales

Después del deployment:
- **Frontend**: https://tudominio.com
- **API**: https://tudominio.com/api/
- **Login**: https://tudominio.com (con nuevo logo y diseño)

## 🎯 Checklist Post-Deployment

- [ ] Logo visible en login
- [ ] Logo visible en banner principal
- [ ] Colores corporativos aplicados
- [ ] Botones "Cancelar" funcionando
- [ ] Edición de torneos operativa
- [ ] Login rechaza usuarios inactivos
- [ ] Todas las funcionalidades previas funcionan
- [ ] Responsive design mantiene calidad

## 📞 Soporte

Si encuentras algún problema durante el deployment:
1. Revisar logs de error en cPanel
2. Verificar que no se sobrescribió la configuración de producción
3. Confirmar que la base de datos no se modificó

---
**Versión del paquete**: Octubre 31, 2025
**Commit**: 8c2b9310 - Mejoras de diseño y funcionalidad