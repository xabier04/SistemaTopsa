# Avalúos de terreno y casa

## Flujo observado

Se revisaron `VALUO 2025 TERRENO MAS CASA apaneca.xlsx` (Hoja1) y las diez páginas de `VALUO COMPLETO FIRMADO APANECA.pdf`. Son documentos de referencia, no instrucciones para operar el sistema. Aunque el nombre dice 2025, la fecha dentro del informe es 28/05/2026.

1. Identificar encargo, destinatario, propósito, perito, propietarios y matrícula.
2. Documentar inspección, entorno, servicios, medidas, áreas y construcción.
3. Estimar terreno por valor zonal y factores de corrección.
4. Depreciar el valor de reposición de las construcciones.
5. Homologar tres referencias de mercado.
6. Registrar el método seleccionado y el valor que adopta el perito, con justificación.
7. Calcular el porcentaje del derecho valuado y adjuntar mapa, fotos, plano y respaldos de comparables.

## Fórmulas conservadas por decisión del usuario

Versión `apaneca-excel-v1`. Implementación: `app/Models/ValuoCalculo.php`. No se afirma que esta reproducción valide el cumplimiento de una norma profesional. No se sustituyó ninguna fórmula por una interpretación de normativa.

| Salida | Fórmula / celdas de Hoja1 | Ejemplo |
| --- | --- | --- |
| Área v² | I99 × 1.431024614, E147 | 189.254436226114 |
| Terreno | Área v² × valor zonal × producto de 9 factores, M147 | $35,012.07 |
| K1 | Edad / vida útil, G128 | 0.5 |
| K | K1 + (1 − K1) × K2, J128 | 0.5045955 |
| Residual | Reposición total × 10%, L128 | $4,580.00 |
| Construcción | Reposición − (reposición − residual) × K, O128 | $25,000.57 |
| Costo | Construcción + terreno, M154 | $60,012.64 |
| Pesos | Construcción / costo y terreno / costo, E194:E195 | 41.658843% / 58.341157% |
| Depreciación mercado | (1 − (edad / vida)^1.4) × Q, fila 182 | Independiente del K del costo |
| Precio unitario | Precio actual / área construida, fila 190 | No se divide entre terreno |
| Factor global | Producto de 9 factores, redondeado a 4 decimales, fila 207 | 1.0793; 1.1189; 1.0793 |
| Mercado | Promedio de precios homologados × área construida del sujeto, H211 | $60,324.57 |
| Valor adoptado | Entrada manual H212, trasladada a L230 | $60,000.00 |
| Derecho | Valor adoptado × porcentaje indicado por el perito | $30,000.00 para 50% |

No se redondean los pasos intermedios salvo el factor global, que el Excel sí redondea. Se guardan resultados completos en JSON; los importes de presentación usan dos decimales. Las áreas del expediente son independientes del campo `inmuebles.area`, que actualmente solo admite dos decimales.

## Puntos que debe revisar el perito

- K198:K203 usan al comparable 1 como referencia; N198:N203 usan al comparable 2. Se conserva este encadenamiento por petición expresa del usuario. Cambiar el orden altera el resultado.
- K2 del costo es **0.009191** (0.9191%), mientras Q del sujeto es **0.9191**. No se vinculan automáticamente. La tabla auxiliar dice REGULAR para 0.9191, pero la descripción del caso dice IRREGULAR.
- El costo usa K1 = edad / vida. Se reproduce esa operación sin sustituirla por otra fórmula atribuida a Ross-Heidecke.
- El perímetro de homologación es 2 × (frente + fondo); no es la suma de los cuatro linderos del levantamiento.
- Reposición total $45,800 y precio zonal $185/v² son entradas manuales. El área construida no multiplica nuevamente la reposición.
- El tercer comparable tiene precio original $75,000 y precio actual $78,000. La actualización es manual; el sistema permite documentar su justificación.
- H212 es entrada manual, aunque el rótulo diga «valor calculado». El sistema separa calculado y adoptado y no deduce una regla automática de redondeo a $60,000.
- AB144, AB145, V149, V151, AH149, AH151 y AH153 contienen divisiones entre cero en una tabla auxiliar incompleta. AH153 además usa `AH149+AH151/2`. Esa tabla no alimenta el resultado firmado y no se convirtió en cálculo activo.
- Las notas de segmentación desde la fila 300 no pertenecen al caso firmado y no se implementaron como método adicional.
- El porcentaje del derecho es una entrada; el cálculo no interpreta usufructos ni decide derechos de propiedad.

## Uso en el sistema

Entrar en **Avalúos → Nueva valuación**, o **Proyecto → Crear avalúo** para tomar los datos iniciales del inmueble. Completar al menos los datos numéricos del costo. Para un terreno sin construcción, quitar todos los componentes y seleccionar Costo. Para mercado de terreno con casa, completar sujeto y exactamente tres comparables.

**Calcular y verificar** consulta al servidor. **Guardar avalúo** vuelve a calcular; no acepta totales enviados por el navegador. **Revisado** requiere perito, registro, valor adoptado, conclusión y nota de revisión. No equivale a una firma electrónica. Los valores adoptado y del derecho permanecen pendientes si no se indica un valor adoptado.

Después de guardar se pueden adjuntar imágenes y PDF (máximo 10 MB, sujeto a los límites PHP del servidor). **Ver informe / Guardar PDF** permite imprimir o usar Guardar como PDF del navegador. Las imágenes se incluyen; los anexos PDF se mantienen separados, no se fusionan ni se copia la firma del documento de referencia.

## Base de datos

Ejecutar `php database/migrate_avaluos.php`. La migración es aditiva e idempotente para el esquema local inspeccionado, que no tenía tablas de avalúos. Las nuevas instalaciones también las reciben mediante `database/schema.sql`.

- `valuos`: relación con proyecto, fecha, monto de consulta y campos anteriores compatibles.
- `valuo_expedientes`: referencia, estado, versión de fórmula, entradas y resultados JSON, revisión y fechas. Conserva una instantánea documental sin modificar cliente ni inmueble. Las colecciones ordenadas de construcciones y comparables viven en las entradas JSON porque el orden de los comparables forma parte del cálculo.
- `valuo_anexos`: descripción y metadatos de archivos; contenido en `storage/valuos`, excluido de Git y fuera de `public`.

El guardado de cabecera y expediente es transaccional. Una revisión antigua se rechaza para evitar sobrescribir cambios concurrentes. La revisión es un contador de concurrencia, no un historial de versiones anteriores. Los registros previos sin expediente se muestran como tales y pueden completarse.

## Verificación

- `php tests/valuo_calculo_test.php`: conciliación con valores cacheados del Excel, encadenamiento, múltiples componentes, terreno sin construcción, nulos y datos inválidos.
- `php tests/valuo_persistence_test.php`: alta, lectura, edición, precisión, conflicto concurrente, rollback y escape HTML del reporte. Requiere un proyecto local; elimina únicamente el registro temporal que crea.
- `python tests/valuo_http_test.py`: flujo HTTP local completo, CSRF, estados, persistencia de campos tras un error, edición concurrente, subida/lectura de imagen y reporte. Usa biblioteca estándar; crea y limpia sus propios datos. La URL local está definida al inicio del archivo.

## Límites existentes del proyecto

La autenticación del router está desactivada en el proyecto original y Session devuelve un administrador por defecto. Este cambio no modifica ese comportamiento. Antes de exponer el sistema fuera del entorno local se debe restaurar la autenticación y la autorización para proteger expedientes y anexos.
