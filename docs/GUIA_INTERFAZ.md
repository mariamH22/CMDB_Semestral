# Guia de Interfaz CMDB Control Center

Esta guia documenta la identidad visual del proyecto sin cambiar logica backend,
rutas, formularios, credenciales, base de datos ni configuracion de servidor.

## Alcance

- La interfaz usa `public/assets/css/app.css` como hoja de estilos principal.
- El comportamiento progresivo usa `public/assets/js/app.js`.
- Las vistas mantienen `url()` y `asset()` para construir enlaces.
- Los formularios conservan sus `method`, `action`, `name` y tokens CSRF.
- No se agrego configuracion de Nginx, Apache, PHP-FPM o MySQL al repositorio.
- `public/.htaccess` se conserva para compatibilidad con Apache/WampServer.

## Layout

La aplicacion distingue dos experiencias:

- Publica: inicio, noticias, login y recuperacion.
- Interna: panel con barra lateral, barra superior, contenido principal y pie.

El layout principal esta en:

- `app/Views/layout/header.php`
- `app/Views/layout/footer.php`
- `app/Views/layout/flash.php`

La navegacion interna respeta los permisos actuales del sistema. La visibilidad de
enlaces depende de las funciones de autenticacion existentes, no de reglas nuevas en
frontend.

## Direccion visual

La interfaz usa una direccion llamada `CMDB Control Center`: fondo claro,
superficies sobrias, navegacion lateral oscura, acento moderado y componentes
orientados a inventario, trazabilidad y control operativo.

La version actual integra rasgos del primer diseno del proyecto: lectura mas
suave, tarjetas limpias, superficies claras, tablas calmadas y microinteracciones
discretas, sin abandonar la nueva identidad Control Center.

## Sistema visual

Los tokens de diseno estan en `:root` dentro de `public/assets/css/app.css`.
Las variables nuevas usan prefijo `--cmdb-*`. Se conservan alias `--color-*`,
`--space-*` y similares para compatibilidad con vistas existentes.

Principales variables:

- Colores: `--cmdb-primary`, `--cmdb-ink`, `--cmdb-accent`, `--cmdb-danger`.
- Superficies: `--cmdb-canvas`, `--cmdb-surface`, `--cmdb-panel`.
- Espaciado: `--cmdb-space-1` a `--cmdb-space-10`.
- Radios: `--cmdb-radius-sm`, `--cmdb-radius-md`, `--cmdb-radius-lg`.
- Sombras: `--cmdb-shadow-sm`, `--cmdb-shadow-md`.
- Capas: `--cmdb-z-sidebar`, `--cmdb-z-overlay`, `--cmdb-z-modal`.

Para mantener consistencia, las nuevas vistas deben reutilizar estas variables y evitar
estilos inline.

## Componentes

Componentes disponibles:

- Botones: `.btn`, `.btn-primary`, `.btn-success`, `.btn-danger`, `.btn-light`.
- Tarjetas: `.card`, `.form-card`, `.stat-card`, `.asset-card`.
- Formularios: `.form-grid`, `.form-group`, `.field-help`, `.check-row`.
- Tablas: `.table-wrap`, `.responsive-table`.
- Estados: `.badge`, `.alert`, `.no-data`.
- Imagenes: `.image-thumb`, `.image-large`, `.gallery`.
- Utilidades: `.button-row`, `.section-spaced`, `.mt-sm`, `.mt-md`, `.mb-md`.

Las tablas deben incluir `caption` y encabezados con `scope="col"` cuando sea posible.
Los formularios deben usar etiquetas visibles asociadas al campo correspondiente.

## Responsive

El panel interno usa barra lateral fija en escritorio y menu lateral deslizable en
pantallas pequenas. El boton de menu usa atributos `aria-expanded` y el foco vuelve al
boton que abrio el menu al cerrarlo.

Las grillas se adaptan con `grid-template-columns` responsivo. Los botones y filtros
permiten salto de linea para evitar desbordamientos en movil.

## JavaScript

`public/assets/js/app.js` agrega mejoras progresivas:

- Apertura y cierre accesible del menu lateral.
- Control de foco dentro del menu lateral movil.
- Breadcrumb visual generado desde la navegacion activa.
- Cierre manual de alertas.
- Confirmacion accesible en modal para acciones con `data-confirm`.
- Boton mostrar/ocultar en campos de contrasena.
- Mensaje de carga en botones submit.
- Validacion visual progresiva con foco al primer campo invalido.
- Contadores para campos con `maxlength`.
- Autoajuste de altura en `textarea`.
- Etiquetas `data-label` para tablas responsivas en movil.
- Indicadores de scroll en tablas anchas.
- Seleccion visual temporal de filas de tabla.
- Foco automatico en alertas importantes.
- Boton progresivo para volver arriba en paginas largas.
- Ayuda visual y vista previa local para seleccion de imagenes.
- Modal accesible para ampliar imagenes con anterior/siguiente.
- Ocultamiento de campos de licencia segun el checkbox existente.

No usa `innerHTML`, `eval`, almacenamiento local, almacenamiento de sesion ni consola.

## Accesibilidad

- Existe enlace de salto a contenido principal.
- Las alertas usan `role="alert"` y `aria-live`.
- Los modales y menu movil responden a la tecla Escape.
- Los estados de navegacion usan `aria-current="page"`.
- Los controles interactivos conservan foco visible.

## Seguridad visual

- Las claves de licencia se mantienen enmascaradas salvo flujo autorizado del backend.
- Los tokens QR no se muestran como texto en la vista de detalle.
- La recuperacion de contrasena no imprime enlaces demo en pantalla.
- No se exponen rutas locales, trazas, secretos ni configuracion de servidor en las vistas.

## Impresion

El CSS incluye reglas `@media print` para limpiar navegacion, botones y fondos al imprimir.
La etiqueta imprimible del activo usa `.print-label` y muestra solo informacion operativa.

## Reglas para futuras mejoras

- No cambiar rutas solo por Ubuntu o Nginx.
- No introducir rutas absolutas de equipos personales.
- No modificar `.htaccess` sin una razon funcional para Apache/WampServer.
- No meter archivos como `nginx.conf`, `default`, `sites-available` o `sites-enabled`.
- No cambiar `include`, `require`, `require_once`, nombres de campos o acciones de formularios por motivos visuales.
- Probar primero en el repo original y sincronizar luego a la copia local usada por Nginx.
