# EduMetrics SV — Plataforma de Gestión Académica y Calificaciones

Sistema web de control escolar, registro de evaluaciones y cálculo automatizado de calificaciones diseñado bajo los lineamientos evaluativos del Ministerio de Educación de El Salvador (**MINED**).

## Stack Tecnológico

* **Backend:** PHP 8.3 · Laravel 11
* **Base de Datos:** PostgreSQL 16
* **Frontend:** Blade · Tailwind CSS · Alpine.js
* **Seguridad y Roles:** Spatie Laravel Permission (RBAC)
* **Reportes Oficiales:** Barryvdh Laravel DomPDF
* **Pruebas Automatizadas:** PHPUnit / Pest (Feature & Unit Tests)

## Reglas de Negocio y Normativa MINED

* **Estructura Evaluativa:** Año lectivo dividido en **4 períodos** con ponderación del **25.00 %** cada uno (validación estricta de suma exacta al 100.00 %).
* **Cálculo Ponderado:** Promedio por período calculado dinámicamente según el porcentaje asignado a cada actividad evaluada (tareas, laboratorios, exámenes).
* **Criterio de Aprobación:** Escala de `0.00` a `10.00` con nota mínima de aprobación de **`6.00`**.
* **Control de Acceso por Rol (RBAC):**
  * **Administrador / Coordinador:** Gestión integral de asignaturas, asignación de carga docente, apertura de secciones, configuración de períodos, matrícula de estudiantes y consulta global de cuadros/actas.
  * **Docente:** Acceso restringido exclusivamente a las asignaturas y secciones bajo su titularidad para crear evaluaciones, digitar notas y exportar sus cuadros de calificación.

## Módulos Principales

1. **Dashboard Analítico:** Indicadores de matrícula activa, tasa de aprobación, promedio institucional y monitoreo de secciones sin actividad.
2. **Evaluaciones y Digitación de Notas:** Creación de actividades con validación de peso por período y captura masiva de calificaciones.
3. **Cuadro de Notas y Reportes PDF:** Consolidado por sección/asignatura con exportación oficial en PDF de **Actas de Calificación** y **Boletas Individuales**.
4. **Directorio y Matrícula Estudiantil:** Registro con validación única de **NIE**, edición de ficha y control de estado activo/inactivo preservando el historial académico.
5. **Gestión Académica:** Administración de materias, asignación de docentes titulares, creación de secciones y ajuste de ponderaciones por período.

## Instalación Local

```bash
git clone https://github.com/davidApM/edumetrics-sv.git
cd edumetrics-sv
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configura las credenciales de **PostgreSQL 16** en el archivo `.env` y ejecuta:

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

## Credenciales de Prueba (Datos Semilla)

| Rol | Correo Electrónico | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | `admin@edumetrics.sv` | `password` |
| **Coordinador** | `coordinador@edumetrics.sv` | `password` |
| **Docente** | `docente@edumetrics.sv` | `password` |

## Pruebas Automatizadas

El proyecto incluye 33 pruebas automatizadas que verifican los bloqueos `403` entre roles, la unicidad de NIE, la integridad de ponderaciones al 100 % y la generación de documentos PDF:

```bash
php artisan test
```

## Autor

Desarrollado por **David** (`@davidApM`) — El Salvador.