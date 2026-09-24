# Pagos, acceso y seguimiento

En una instalación existente ejecutar `php database/migrate_gestion.php`. Es aditiva y puede repetirse. La base local de este proyecto ya está migrada. Las instalaciones nuevas incluyen las tablas en `database/schema.sql`.

## Uso

- **Administrador:** administra los módulos existentes, personal, cuentas, pagos, equipos y tareas.
- **Empleado:** accede a sus proyectos y tareas, registra su avance y cambia su contraseña. Las restricciones se comprueban también en el servidor.
- **Pagos y saldos:** registra abonos, consulta saldos calculados por cliente/proyecto y abre comprobantes para imprimir o guardar como PDF desde el navegador. El saldo del comprobante corresponde al momento en que se registró. Se rechazan montos no positivos, con más de dos decimales o superiores al saldo. Una referencia por formulario evita duplicados al reenviar.
- **Proyectos → detalle:** asigna empleados activos al equipo. Una asignación con tareas se conserva para mantener su seguimiento.
- **Tareas y avances:** asigna una tarea, descripción y fecha límite a un integrante del equipo. El empleado puede actualizar su porcentaje y observaciones. 0% es pendiente, 1–99% en proceso y 100% completada. El resumen muestra el promedio de tareas; el empleado solo ve las suyas. El filtro de proyectos activos excluye los finalizados.
- **Recuperación:** el enlace del login registra una solicitud. El administrador la ve en Usuarios, verifica la identidad y genera una clave temporal para entregarla mediante el canal habitual. No envía correos automáticamente. El siguiente acceso exige cambiarla; las sesiones anteriores dejan de funcionar.

Las cuentas existentes conservan el hash de su contraseña. Se eliminó el acceso alternativo codificado: solo se acepta la contraseña que coincide con el hash. El hash incluido originalmente en el esquema corresponde a `password`; cambie esa clave desde la cuenta administradora.

## Verificación

`php tests/gestion_test.php` prueba por HTTP contra `http://sistematopsa-new.test` (configurable con `TOPSA_TEST_URL`), usando la misma base configurada en el proyecto. Crea cuentas y registros propios de prueba y los elimina en `finally`. Ejecutar únicamente en el entorno local de desarrollo.

Comprueba login, separación de roles, rechazo de métodos internos, CSRF, pagos inválidos y duplicados, saldos, comprobantes, aislamiento de tareas, avances, escape HTML y recuperación. No modifica cuentas ni proyectos existentes.
