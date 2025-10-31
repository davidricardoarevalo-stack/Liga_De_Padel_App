# 🚀 INSTRUCCIONES FINALES - SUBIR A BLUEHOST

## 📂 UBICACIÓN DE ARCHIVOS LISTOS
**Carpeta completa preparada:** `DEPLOYMENT_READY/LPBApp/`

## 🎯 PASOS ESPECÍFICOS PARA BLUEHOST

### **PASO 1: Acceder a File Manager**
1. Ir a cPanel de Bluehost
2. Buscar "File Manager" y hacer clic
3. Navegar a `public_html/`

### **PASO 2: Subir carpeta LPBApp**
1. En File Manager, hacer clic en "Upload"
2. **Comprimir primero:** Comprimir `DEPLOYMENT_READY/LPBApp/` en un ZIP
3. Subir el archivo ZIP a `public_html/`
4. Hacer clic derecho en el ZIP → "Extract"
5. Verificar que se creó `/public_html/LPBApp/`

### **PASO 3: Configurar permisos**
1. Seleccionar carpeta `LPBApp/api/`
2. Clic derecho → "Permissions" → `755`
3. Seleccionar archivo `LPBApp/api/.env`
4. Clic derecho → "Permissions" → `644`

### **PASO 4: Verificar estructura**
Verificar que existe:
```
/public_html/LPBApp/
├── index.html       ✅
├── static/          ✅
├── .htaccess        ✅
└── api/
    ├── .env         ✅
    ├── vendor/      ✅
    ├── public/      ✅
    └── bootstrap/   ✅
```

## ✅ VERIFICACIÓN POST-SUBIDA

### **1. Test básico PHP:**
Visitar: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_api.php`
**Esperado:** `{"status": "success", "message": "API funcionando correctamente"}`

### **2. Test dependencias:**
Visitar: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_dependencies.php`
**Esperado:** `{"status": "success", "ready_for_production": true}`

### **3. Interface de prueba completa:**
Visitar: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_interface.html`
**Función:** Interface web para probar todos los endpoints con botones

### **4. Test login directo (POST):**
URL: `https://ligadepadeldebogotaoficial.com/LPBApp/api/test_login.php`
**Método:** POST con `{"email": "app@app.com", "password": "123"}`
**Esperado:** `{"success": true, "token": "..."}`

### **5. Test frontend:**
Visitar: `https://ligadepadeldebogotaoficial.com/LPBApp/`
**Esperado:** Aplicación React carga correctamente

### **6. Test login completo (a través del router):**
**⚠️ IMPORTANTE:** `/api/login` requiere POST, no GET
- Usar la interface de prueba o el frontend React
- Email: `app@app.com`
- Password: `123`
- **Esperado:** Login exitoso a través del router principal

## 🔧 CONFIGURACIÓN INCLUIDA

### ✅ **Frontend (React):**
- Build optimizado con `PUBLIC_URL=/LPBApp`
- Rutas configuradas para subdirectorio
- API apunta a `/LPBApp/api/`

### ✅ **Backend (PHP sin Docker):**
- vendor/ con Firebase JWT
- .env con credenciales Bluehost:
  - DB: `ajkyinmy_liga_padel_app`
  - Usuario: `ajkyinmy_user_app`
  - Password: `pwd_l1g4_app`
- APP_KEY: `base64:THFXfPSG/Mq5ld45YF7xcBOjCGfQ9zS6QtiXt3z+Mfw=`
- JWT_SECRET: `bf21993ce8549507b859a5c6eeab1fefe6b757d51a2116c7714bc7f83d6bf5493e78abed20d022ed12937caf9318c0d5f0f320e1ae765f04f8d45c3e326430b4`

### ✅ **Redirecciones (.htaccess):**
- API: `/LPBApp/api/login` → `api/public/index.php`
- SPA: rutas React manejadas correctamente

## 🆘 SI ALGO FALLA

### **Error 404 en API:**
- Verificar que existe `/public_html/LPBApp/api/public/index.php`
- Verificar permisos de carpeta `api/` (755)

### **Error 500 en API:**
- Verificar que existe `/public_html/LPBApp/api/.env`
- Verificar que existe `/public_html/LPBApp/api/vendor/`
- Revisar logs de error en cPanel

### **Frontend no carga:**
- Verificar que existe `/public_html/LPBApp/index.html`
- Verificar que existe `/public_html/LPBApp/.htaccess`

## 🎉 ¡LISTO!
Una vez subido todo, tu aplicación estará disponible en:
**https://ligadepadeldebogotaoficial.com/LPBApp/**