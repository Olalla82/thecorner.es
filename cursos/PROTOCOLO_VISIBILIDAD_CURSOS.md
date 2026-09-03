# 📋 PROTOCOLO: VISIBILIDAD DE CURSOS EN WEB

**Fecha:** 5 de Mayo de 2026  
**Versión:** 2.0 - CORREGIDO ✅  
**Equipo:** The Corner - Gestión de Cursos

---

## 🎯 RESUMEN EJECUTIVO

Este documento explica **qué checks deben estar activos** en la base de datos para que un curso aparezca en:
1. **Listado normal de cursos** (cursos-gratuitos.php)
2. **Sección de CURSOS DESTACADOS** (parte superior de la página)

La lógica es **diferente para FOAP y CONSORCI**, pero ahora es **consistente** en ambas secciones.

---

## 📊 TABLA DE CHECKS REQUERIDOS

### ✅ Para aparecer en **LISTADO NORMAL**

| Tipo | `c.mostrar_web` (curso) | `g.mostrar_web` (grupo) | `g.data_final` | Notas |
|------|-------------------------|-------------------------|----------------|-------|
| **CONSORCI** | ❌ No importa (puede estar en 0) | ✅ Debe ser 1 | ≥ Hoy | Solo importa la visibilidad del GRUPO |
| **FOAP** | ✅ O este = 1 | ✅ O este = 1 | ≥ Hoy | Con que UNO esté en 1, se muestra |

### ⭐ Para aparecer en **CURSOS DESTACADOS** (✅ CORREGIDO)

| Tipo | `g.curs_destacat` | `c.mostrar_web` | `g.mostrar_web` | `g.data_final` | ¿Funciona? |
|------|-------------------|-----------------|-----------------|----------------|------------|
| **CONSORCI** | ✅ 1 | ❌ No importa | ✅ Debe ser 1 | ≥ Hoy | ✅ **SÍ** |
| **FOAP** | ✅ 1 | ✅ O este = 1 | ✅ O este = 1 | ≥ Hoy | ✅ **SÍ** |

---

## � IMPLEMENTACIÓN TÉCNICA

### Query para CURSOS DESTACADOS (✅ IMPLEMENTADA)

```sql
SELECT DISTINCT
    c.id as id_curs,
    c.nom_comercial,
    TRIM(SUBSTRING_INDEX(c.nom_comercial, '|', -1)) as nom_curs,
    g.curs_destacat AS destacat,
    g.id as id_grup,
    g.data_inici,
    g.data_final,
    c.tipus_subvencionada,
    c.especialitat_formativa,
    g.modalitat
FROM gen_cursos c
INNER JOIN gen_grups g ON g.curs = c.id
WHERE g.curs_destacat = 1
    AND g.data_final >= CURDATE()
    AND (
        -- Para CONSORCI: solo verificar grupo
        (c.tipus_subvencionada = 'CONSORCI' AND g.mostrar_web = 1) 
        OR 
        -- Para FOAP: verificar curso O grupo
        (c.tipus_subvencionada != 'CONSORCI' AND (c.mostrar_web = 1 OR g.mostrar_web = 1))
    )
ORDER BY g.data_inici ASC
LIMIT 3
```

### Explicación de la lógica:

1. **`g.curs_destacat = 1`** → Solo grupos marcados como destacados
2. **`g.data_final >= CURDATE()`** → Solo grupos que NO han finalizado
3. **Para CONSORCI** (`c.tipus_subvencionada = 'CONSORCI'`):
   - ✅ Solo verifica `g.mostrar_web = 1`
   - ❌ Ignora `c.mostrar_web` (porque CONSORCI siempre tiene c.mostrar_web = 0)
4. **Para FOAP** (cualquier otro tipo):
   - ✅ Verifica `c.mostrar_web = 1` **O** `g.mostrar_web = 1`
   - ✅ Con que UNO de los dos esté activo, se muestra

### Archivos modificados:

- ✅ `cursos-gratuitos.php` (línea ~1515-1545)
- ✅ `cursos-gratuitos-ca.php` (línea ~28-60)

---

## 📖 GUÍA RÁPIDA PARA EL EQUIPO

### ✅ Para que un curso aparezca en **LISTADO NORMAL**:

#### Si es CONSORCI:
1. ✅ `g.mostrar_web = 1` (grupo visible)
2. ✅ `g.data_final >= Hoy` (no finalizado)
3. ❌ `c.mostrar_web` → No importa

#### Si es FOAP:
1. ✅ `c.mostrar_web = 1` **O** `g.mostrar_web = 1` (con uno vale)
2. ✅ `g.data_final >= Hoy` (no finalizado)

---

### ⭐ Para que un curso aparezca en **DESTACADOS**:

#### Si es CONSORCI:
1. ✅ `g.curs_destacat = 1` (marcar como destacado)
2. ✅ `g.mostrar_web = 1` (grupo visible)
3. ✅ `g.data_final >= Hoy` (no finalizado)
4. ❌ `c.mostrar_web` → No importa

**Ejemplo SQL:**
```sql
UPDATE gen_grups 
SET curs_destacat = 1 
WHERE id = 4613;  -- Grupo del curso CONSORCI
```

#### Si es FOAP:
1. ✅ `g.curs_destacat = 1` (marcar como destacado)
2. ✅ `c.mostrar_web = 1` **O** `g.mostrar_web = 1` (con uno vale)
3. ✅ `g.data_final >= Hoy` (no finalizado)

**Ejemplo SQL:**
```sql
UPDATE gen_grups 
SET curs_destacat = 1 
WHERE id = 1234;  -- Grupo del curso FOAP

-- Asegurarse que al menos uno de estos esté activo:
-- c.mostrar_web = 1 (en tabla gen_cursos)
-- O g.mostrar_web = 1 (en tabla gen_grups)
```

---

## 🔄 DESPUÉS DE CAMBIOS

**IMPORTANTE:** Después de marcar/desmarcar destacados, limpiar la caché:
- Panel: http://localhost/cursos/cache-admin.php
- Botón: "🗑️ Limpiar Todo"

---

## 🔍 DIAGNÓSTICO RÁPIDO

Si un curso marcado como destacado **NO aparece**, verificar:

### ✔️ Checklist de diagnóstico:

1. **¿Está marcado como destacado?**
   ```sql
   SELECT g.curs_destacat FROM gen_grups g WHERE g.id = [ID_GRUPO]
   -- Debe ser 1
   ```

2. **¿Está visible el grupo?**
   ```sql
   SELECT g.mostrar_web FROM gen_grups g WHERE g.id = [ID_GRUPO]
   -- Debe ser 1
   ```

3. **¿Ha finalizado el curso?**
   ```sql
   SELECT g.data_final FROM gen_grups g WHERE g.id = [ID_GRUPO]
   -- Debe ser >= HOY
   ```

4. **¿Es CONSORCI?**
   ```sql
   SELECT c.tipus_subvencionada, c.mostrar_web 
   FROM gen_cursos c 
   WHERE c.id = [ID_CURSO]
   -- Si tipus_subvencionada = 'CONSORCI' y c.mostrar_web = 0, 
   -- con la lógica actual NO aparecerá (problema conocido)
   ```

5. **¿Empezó hace más de 30 días?**
   ```sql
   SELECT g.data_inici, DATEDIFF(g.data_inici, CURDATE()) as dias
   FROM gen_grups g WHERE g.id = [ID_GRUPO]
   -- Si dias < -30, con la lógica actual NO aparecerá
   ```

---

## 🧪 SCRIPT DE DIAGNÓSTICO

Usar el archivo: **`test-destacados.php`**

Acceder desde: `http://localhost/cursos/test-destacados.php`

Este script muestra:
- ✅ Todos los grupos marcados como destacados
- ❌ Por qué no aparecen (si aplica)
- 🔍 Comparación entre query actual vs query mejorada

---

## 📝 RESUMEN PARA DECISIÓN

### Estado actual:
- ❌ Los cursos CONSORCI destacados **NO funcionan**
- ⚠️ Los cursos FOAP destacados solo funcionan si `c.mostrar_web = 1`
- ❌ Cursos que empezaron hace >30 días NO aparecen (aunque sigan activos)

### Acción recomendada:
✅ **Implementar la Opción 1** (corregir la query de destacados)

**Beneficios:**
- ✅ CONSORCI funcionará correctamente
- ✅ Consistencia con el listado normal
- ✅ Todos los cursos activos destacados se mostrarán
- ✅ No requiere cambios manuales en cada curso

**Impacto:**
- ⚠️ Modificación en 2 archivos: `cursos-gratuitos.php` y `cursos-gratuitos-ca.php`
- ✅ Sin cambios en base de datos
- ✅ Compatible con datos existentes

---

## 🎯 CHECKLIST DE IMPLEMENTACIÓN

Si se aprueba implementar la corrección:

- [ ] Hacer backup de `cursos-gratuitos.php`
- [ ] Hacer backup de `cursos-gratuitos-ca.php`
- [ ] Aplicar cambio en query de destacados (ES)
- [ ] Aplicar cambio en query de destacados (CA)
- [ ] Limpiar caché: `cache-admin.php` → "Limpiar Todo"
- [ ] Verificar en: `http://localhost/cursos/cursos-gratuitos.php`
- [ ] Verificar en: `http://localhost/cursos/ca/cursos-gratuitos.php`
- [ ] Probar con: `test-destacados.php`

---

**Documento creado por:** GitHub Copilot  
**Revisado por:** [Pendiente]  
**Aprobado por:** [Pendiente]
