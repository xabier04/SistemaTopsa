# Empleados y usuarios con contraseña temporal

1. Registre el empleado desde Personal → Empleados.
2. Use Crear usuario en su fila o Personal → Usuarios → Nuevo Usuario y seleccione el empleado.
3. Al guardar se genera una contraseña aleatoria de 20 caracteres. La base de datos guarda únicamente el hash bcrypt y el indicador `requiere_cambio_contrasena`.
4. La contraseña se presenta una sola vez en la misma sesión (la entrega pendiente vence tras 10 minutos). Los enlaces de correo y WhatsApp preparan el mensaje; el operador confirma el envío en su aplicación. No hay envío automático, proveedor SMTP ni API de WhatsApp configurados. WhatsApp usa el teléfono del empleado con prefijo 503.
5. El destinatario abre `/usuario/cambiarClave` e introduce correo, contraseña actual y nueva contraseña con confirmación. El cambio comprueba la contraseña vigente, requiere una cuenta activa e invalida la clave anterior. Las contraseñas nunca se restauran tras un error de formulario.
6. Desde Editar Usuario se puede generar otra contraseña temporal. Editar datos generales no modifica la clave.

El enlace compartido usa la dirección del sistema. Un dominio local `.test` requiere acceso y resolución desde el dispositivo del destinatario.

## Instalación y validación

Ejecutar `php database/migrate_usuarios_temporales.php` sobre bases existentes; la migración es aditiva y repetible. El esquema inicial también incluye la columna.

Prueba local: `python tests/usuarios_temporales_http_test.py`. Usa registros temporales y los elimina al terminar; no envía mensajes.

## Alcance actual

El login y las comprobaciones de autorización del router permanecen en su estado previo, desactivados. No se ha implementado control de acceso nuevo ni la obligación de cambiar la contraseña al iniciar sesión. El futuro login deberá aplicar roles y consultar `requiere_cambio_contrasena` para exigir el cambio. El estado «temporal» indica que está pendiente de cambio; la contraseña no tiene vencimiento automático.

Referencia del enlace de WhatsApp: https://faq.whatsapp.com/5913398998672934
