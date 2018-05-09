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