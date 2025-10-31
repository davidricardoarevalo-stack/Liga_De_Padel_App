#!/bin/bash

# Script de Despliegue para Bluehost
# Liga de Padel Application

echo "🚀 Iniciando despliegue en Bluehost..."

# 1. Backup de la aplicación actual (si existe)
if [ -d "public_html_backup" ]; then
    echo "📦 Limpiando backup anterior..."
    rm -rf public_html_backup
fi

if [ -d "public_html" ]; then
    echo "📦 Creando backup de la aplicación actual..."
    cp -r public_html public_html_backup
fi

# 2. Crear estructura de directorios
echo "📁 Creando estructura de directorios..."
mkdir -p public_html
mkdir -p public_html/api

# 3. Copiar Frontend (React Build)
echo "⚛️ Desplegando Frontend React..."
cp -r web-app/build/* public_html/

# 4. Copiar Backend (PHP/Lumen)
echo "🐘 Desplegando Backend PHP..."
cp -r api/* public_html/api/
cp api/.env.production public_html/api/.env

# 5. Configurar permisos
echo "🔐 Configurando permisos..."
chmod -R 755 public_html
chmod -R 644 public_html/api/storage
chmod -R 644 public_html/api/bootstrap/cache

# 6. Instalar dependencias PHP (si composer está disponible)
echo "📦 Instalando dependencias PHP..."
cd public_html/api
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader
else
    echo "⚠️ Composer no disponible. Subir carpeta vendor manualmente."
fi

# 7. Configurar base de datos
echo "🗄️ Configurando base de datos..."
echo "Ejecutar manualmente en phpMyAdmin:"
echo "1. Crear base de datos"
echo "2. Importar estructura desde migration_from_sqlite.sql"
echo "3. Configurar .env con credenciales de Bluehost"

echo "✅ Despliegue completado!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Subir contenido de public_html/ al directorio raíz de tu hosting"
echo "2. Configurar base de datos MySQL en Bluehost"
echo "3. Actualizar .env con credenciales reales"
echo "4. Ejecutar migraciones de base de datos"
echo "5. Configurar dominio para apuntar al directorio público"