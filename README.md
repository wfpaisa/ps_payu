# PayU Latam - Webcheckout - Prestashop 1.7

Módulo para pagos en línea por medio de PayU Latam en modo Webcheckout(se redirige a la pasarela de pagos de PayU y una vez se 
procesa el pedido se retorna una respuesta al Prestashop), este módulo fue desarrollado para Colombia, pero debería funcionar 
en: (Argentina, Brasil, Chile, Colombia, México, Panamá, Perú)

## Notas

Se toma la base de ejemplo `https://github.com/PrestaShop/paymentexample` dada en la documentación de [Prestashop](http://doc.prestashop.com/display/PS17/Creating+a+PrestaShop+1.7+Payment+Module)  
y se adapta las funcionalidades [Webcheckout](http://developers.payulatam.com/es/web_checkout/integration.html) de PayU; Ejemplo de funcionamiento: `https://github.com/wfpaisa/payu-raw`


## Traducciones

Las traducciones están el español Colombia (cb), las puedes cambiar al idioma que necesites ejemplo:

- `config_cb.xml.*` -> `config_es.xml`
- `translations/cb.php` -> `translations/es.php`

## Instalación
- Descargar el contenido de este [repositorio](https://github.com/wfpaisa/ps_payu/archive/master.zip) en un folder llamado `ps_payu` dentro de la carpeta de modulos.
- En el administrador de Prestashop ir a la sección de módulos y buscar por "PayU" e instalar.
- Entrar en las configuraciones del módulo e ingresar los datos solicitados.
- Revisar si esta activo en el administrador/pago/preferencias/Restricciones por transportista  y activar en todos los transportistas
- Ir a la configuración técnica de payu e ingresar los datos:
	- URL de respuesta: https://midominio.com/module/ps_payu/response
	- URL de confirmación: https://midominio.com/module/ps_payu/confirmation

## Pruebas de compras
- En la configuración del módulo habilitar el modo Test en Si
- Buscar generadores de códigos de creditcards.
- Para probar los estados de pago se puede realizar una compra normal y en el paso de la pasarela de pagos escoger la credicard a la cual se le genero el código y en el campo del nombre de usuario ingresar: APPROVED, REJECTED, PENDING segun el caso.
 