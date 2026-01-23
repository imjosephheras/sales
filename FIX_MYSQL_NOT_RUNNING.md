# 🔴 PROBLEMA: Contract Generator Vacío

## 🔍 DIAGNÓSTICO

**Problema:** Las solicitudes enviadas desde el formulario NO aparecen en el Contract Generator.

**Causa raíz:** MySQL/MariaDB **NO ESTÁ CORRIENDO** en el sistema.

### Evidencia:
```bash
# Error al conectar a la base de datos:
Error: SQLSTATE[HY000] [2002] No such file or directory
Error: SQLSTATE[HY000] [2002] Connection refused

# MySQL socket no existe:
/var/run/mysqld/ - Directory does not exist

# No hay proceso MySQL corriendo:
ps aux | grep mysql  # No results
```

## 🛠️ SOLUCIONES

### SOLUCIÓN 1: Iniciar MySQL (Método Estándar)

Para sistemas Ubuntu/Debian:
```bash
# Opción A: systemctl
sudo systemctl start mysql
sudo systemctl enable mysql  # Para que inicie automáticamente

# Opción B: service
sudo service mysql start

# Verificar estado:
sudo systemctl status mysql
```

Para sistemas CentOS/RHEL (MariaDB):
```bash
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo systemctl status mariadb
```

### SOLUCIÓN 2: Iniciar MySQL en Docker (si aplica)

Si tu entorno usa Docker/Docker Compose:

```bash
# Buscar contenedores
docker ps -a

# Si hay un contenedor MySQL/MariaDB, iniciarlo:
docker start <container_name>

# O si hay docker-compose.yml:
docker-compose up -d mysql
```

### SOLUCIÓN 3: Instalar MySQL (si no está instalado)

#### Ubuntu/Debian:
```bash
sudo apt update
sudo apt install mysql-server -y
sudo systemctl start mysql
sudo mysql_secure_installation  # Opcional pero recomendado
```

#### CentOS/RHEL:
```bash
sudo yum install mariadb-server -y
sudo systemctl start mariadb
sudo mysql_secure_installation
```

### SOLUCIÓN 4: Verificar configuración PHP

El archivo `/home/user/sales/contract_generator/contract_generator/config/db_config.php` tiene:
```php
define('DB_HOST', 'localhost');  // Puede ser '127.0.0.1' o 'mysql' en Docker
define('DB_NAME', 'form');
define('DB_USER', 'root');
define('DB_PASS', '');  // Sin contraseña
```

Si tu MySQL está en Docker o usa un host diferente, actualiza `DB_HOST`.

## ✅ VERIFICACIÓN PASO A PASO

### 1. Verificar que MySQL está corriendo
```bash
# Verificar proceso
ps aux | grep mysql

# Verificar puerto 3306
netstat -tlnp | grep 3306
# O
ss -tlnp | grep 3306

# Debería mostrar algo como:
# tcp  0  0  127.0.0.1:3306  0.0.0.0:*  LISTEN  1234/mysqld
```

### 2. Probar conexión
```bash
# Desde línea de comandos
mysql -u root -p
# Presiona Enter (sin contraseña) si DB_PASS está vacío

# O desde PHP:
php -r "try { \$pdo = new PDO('mysql:host=localhost;dbname=form', 'root', ''); echo 'OK\n'; } catch (Exception \$e) { echo 'Error: ' . \$e->getMessage() . '\n'; }"
```

### 3. Verificar que la base de datos 'form' existe
```bash
mysql -u root -e "SHOW DATABASES;"

# Debería listar:
# +--------------------+
# | Database           |
# +--------------------+
# | form               |
# | mysql              |
# | ...                |
# +--------------------+
```

### 4. Si 'form' NO existe, crearla:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS form CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Importar la tabla 'requests' (si no existe)
```bash
cd /home/user/sales
mysql -u root form < create_requests_table.sql

# Verificar:
mysql -u root -e "USE form; SHOW TABLES;"
# Debería mostrar 'requests' entre las tablas
```

### 6. Probar el endpoint de solicitudes pendientes
```bash
cd /home/user/sales
php test_db.php

# Debería mostrar:
# === Testing Database Connection ===
# Total requests in database: X
# === Requests by Status ===
# pending: X
# === Recent Pending Requests ===
# ...
```

### 7. Probar en el navegador

Abre: `http://localhost/sales/contract_generator/contract_generator/index.php`

- **Si MySQL está corriendo:** Verás las solicitudes en el panel izquierdo "Pending Tasks"
- **Si MySQL NO está corriendo:** Verás "No pending tasks" aunque hayas enviado solicitudes

## 🔄 FLUJO COMPLETO

```
Usuario llena formulario
    ↓
Submit → form_contract/enviar_correo.php
    ↓
┌─────────────────────────────────────────┐
│ ¿MySQL está corriendo?                  │
├─────────────────────────────────────────┤
│ ✅ SÍ  → Guarda en DB (status=pending)  │
│         → Redirecciona a Contract Gen   │
│         → Aparece en inbox              │
│                                         │
│ ❌ NO  → Falla silenciosamente          │
│         → Muestra página de éxito       │
│         → NO guarda en DB               │
│         → NO aparece en Contract Gen    │
└─────────────────────────────────────────┘
```

## 🎯 RESULTADO ESPERADO

Después de iniciar MySQL y tener la BD configurada:

1. ✅ Puedes enviar formularios desde `/sales/form_contract/`
2. ✅ Los datos se guardan en `form.requests` con status='pending'
3. ✅ Al abrir Contract Generator, las solicitudes aparecen en el panel izquierdo
4. ✅ Puedes hacer click en una solicitud y editarla
5. ✅ Puedes generar PDFs desde el Contract Generator

## 📞 AYUDA ADICIONAL

Si después de iniciar MySQL el problema persiste:

1. **Revisa logs de MySQL:**
   ```bash
   tail -f /var/log/mysql/error.log
   ```

2. **Revisa logs de PHP:**
   ```bash
   tail -f /var/log/php/error.log
   # O en Apache:
   tail -f /var/log/apache2/error.log
   ```

3. **Verifica permisos del usuario MySQL:**
   ```bash
   mysql -u root -e "GRANT ALL PRIVILEGES ON form.* TO 'root'@'localhost'; FLUSH PRIVILEGES;"
   ```

4. **Habilita display_errors en PHP** (solo para desarrollo):
   Edita `php.ini`:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```

---

**Creado:** 2026-01-23
**Issue:** MySQL not running - Contract Generator shows empty
**Branch:** `claude/fix-contract-generator-display-sibPi`
