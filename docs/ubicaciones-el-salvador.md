# Catálogo de ubicaciones

`config/el_salvador.json` contiene 14 departamentos, 44 municipios y 262 distritos, agrupados según el artículo 1 del Decreto 762.

Fuente: https://transparencia.mh.gob.sv/downloads/pdf/700-DGCG-LY-2024-001.pdf (páginas 6–13, consultado el 9 de septiembre de 2026). Se normalizan mayúsculas y tildes para presentación.

Para bases existentes ejecutar `php database/migrate_ubicaciones.php`. Es repetible y agrega únicamente las tres columnas de ubicación. Las direcciones anteriores se conservan; la ubicación se completa al editar el inmueble. Las instalaciones nuevas incluyen las columnas en `database/schema.sql`.

Clientes permite teléfono y correo vacíos; cuando se proporcionan se valida su formato. Clientes e inmuebles conservan los campos correctos tras errores de validación y vacían únicamente los campos señalados por el servidor, tanto al crear como al editar.
