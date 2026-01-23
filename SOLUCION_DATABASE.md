# Solución: Problema de Guardado en Base de Datos (Hoodvent & Kitchen Cleaning)

## 🔍 PROBLEMA IDENTIFICADO

Los datos de "Hoodvent & Kitchen Cleaning" (Pregunta 19) NO se estaban guardando correctamente en la base de datos porque:

1. **La estructura de las tablas NO coincidía con los datos del formulario**
2. **Había campos en la BD que NO se usaban** (monthly_cost, annual_cost, hours_per_service, etc.)
3. **Faltaban campos que SÍ se necesitaban** (service_type, service_time, subtotal)

## 📊 COMPARACIÓN

### ❌ ANTES (Tabla incorrecta)

```sql
CREATE TABLE `hood_vent_costs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_id` int NOT NULL,
  `service_number` int DEFAULT '1',
  `service_description` varchar(200),  -- ❌ nombre incorrecto
  `frequency` varchar(50),
  `hours_per_service` decimal(5,2),    -- ❌ NO SE USA
  `rate_per_hour` decimal(8,2),        -- ❌ NO SE USA
  `monthly_cost` decimal(10,2),        -- ❌ NO SE USA
  `annual_cost` decimal(10,2),         -- ❌ NO SE USA
  `supplies_cost` decimal(10,2),       -- ❌ NO SE USA
  `total_cost` decimal(10,2),          -- ❌ NO SE USA
  ...
)
```

### ✅ DESPUÉS (Tabla correcta)

```sql
CREATE TABLE `hood_vent_costs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `form_id` int NOT NULL,
  `service_number` int DEFAULT '1',
  `service_type` varchar(100),         -- ✅ NUEVO: "Bar Cleaning", "Vent Hood"
  `service_time` varchar(50),          -- ✅ NUEVO: "1 Day", "1-2 Days", "4 Days"
  `frequency` varchar(50),             -- ✅ OK: "Weekly", "Quarterly"
  `description` varchar(200),          -- ✅ OK: "1", "2", "3", "4"
  `subtotal` decimal(10,2),            -- ✅ NUEVO: 500, 200, 400, 1500
  ...
)
```

## 🛠️ ARCHIVOS MODIFICADOS

### 1. **save_draft.php** (Código PHP para guardar)
- ✅ Actualizado para usar los campos correctos
- ✅ Ahora guarda: `service_type`, `service_time`, `frequency`, `description`, `subtotal`

### 2. **fix_database_schema.sql** (Script SQL de migración)
- ✅ Elimina columnas que no se usan
- ✅ Agrega columnas nuevas que sí se necesitan
- ✅ Renombra `service_description` a `description`

### 3. **migrate_database.php** (Script PHP alternativo)
- ✅ Script PHP interactivo para ejecutar la migración
- ✅ Muestra el progreso paso a paso

## 📝 CÓMO APLICAR LA SOLUCIÓN

### OPCIÓN 1: Usando phpMyAdmin (Recomendado)

1. Abre **phpMyAdmin**
2. Selecciona la base de datos **form**
3. Ve a la pestaña **SQL**
4. Abre el archivo `fix_database_schema.sql`
5. Copia todo el contenido
6. Pégalo en la ventana de SQL
7. Haz clic en **"Ejecutar"** (Go)

### OPCIÓN 2: Usando MySQL Command Line

```bash
cd /home/user/sales
mysql -u root -p form < fix_database_schema.sql
```

### OPCIÓN 3: Usando el script PHP

```bash
cd /home/user/sales/form_contract
php migrate_database.php
```

## ✅ VERIFICACIÓN

Después de ejecutar la migración, verifica que las tablas tengan la estructura correcta:

```sql
USE form;
DESCRIBE hood_vent_costs;
DESCRIBE kitchen_cleaning_costs;
DESCRIBE janitorial_services_costs;
```

Deberías ver estos campos en las 3 tablas:
- `id`
- `form_id`
- `service_number`
- `service_type` ← NUEVO
- `service_time` ← NUEVO
- `frequency`
- `description` (antes service_description)
- `subtotal` ← NUEVO
- `created_at`

## 🧪 PRUEBA

1. Abre el formulario
2. Llena la sección "19. Hoodvent & Kitchen Cleaning"
3. Agrega varios servicios con diferentes datos
4. Guarda el formulario
5. Verifica en la base de datos que los datos se guardaron correctamente:

```sql
SELECT * FROM hood_vent_costs WHERE form_id = [TU_FORM_ID];
SELECT * FROM kitchen_cleaning_costs WHERE form_id = [TU_FORM_ID];
```

## 📌 NOTAS IMPORTANTES

1. ⚠️ **Haz un backup de tu base de datos ANTES de ejecutar la migración**
2. ⚠️ Si ya tienes datos guardados con la estructura antigua, se perderán al eliminar las columnas
3. ✅ El código JavaScript (`index.php`) ya estaba correcto - no necesita cambios
4. ✅ Esta solución también arregla la tabla `kitchen_cleaning_costs`
5. ✅ La tabla `janitorial_services_costs` ya tenía la estructura correcta

## 🎯 RESULTADO

Después de aplicar esta solución:
- ✅ Los datos se guardarán correctamente en la base de datos
- ✅ Los datos se cargarán correctamente al editar un formulario
- ✅ Las 3 tablas (janitorial, kitchen, hood) tendrán una estructura consistente
- ✅ El formulario funcionará perfectamente

---

**Fecha:** 2026-01-23
**Issue:** Problema de guardado en base de datos
**Rama:** `claude/fix-database-saving-5dMMP`
