<?php
class Ps_PayUValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'ps_payu') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');

        $currency               = $this->context->currency;
        $currency_iso           = $currency->iso_code;
        $total                  = (float)$cart->getOrderTotal(true, Cart::BOTH);
        $order_state_approved   = 23;
        $order_state_rejected   = 23;
        $order_state_pending    = 23;
        $mailVars       = array(
            '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
            '{bankwire_details}' => nl2br(Configuration::get('BANK_WIRE_DETAILS')),
            '{bankwire_address}' => nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
        );


        $this->module->validateOrder($cart->id, $order_state_approved, $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);

        // PayU Data
        $order                  = new Order($this->module->currentOrder); // $this->module->currentOrder is generate after validateOrder
        $order_reference        = ($order->reference ? $order->reference : 0);
        $url                    = 'https://checkout.payulatam.com/ppp-web-gateway-payu/'; // Producción
        // $url                 = 'https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/'; // Sandbox
        $ApiKey                 = 'aK7zFVLdsKbDe9495WaNDMb3j2';
        $merchantId             = '322164';
        $accountId              = '427063';
        $firma                  = "$ApiKey~$merchantId~$order_reference~$total~$currency_iso";
        $firmaMd5               = md5($firma);
        
        $this->context->smarty->assign([
            'url'               => $url,
            'ApiKey'            => $ApiKey,
            'merchantId'        => $merchantId,
            'accountId'         => $accountId,
            'description'       => $this->module->l('Online payment'),
            'referenceCode'     => $order_reference,
            'amount'            => $total,
            'tax'               => '0', // La tienda incluye el tax en el total
            'taxReturnBase'     => '0', // no aplica
            'currencyIso'       => $currency_iso,
            'test'              => '0',
            'buyerEmail'        => $customer->email,
            'responseUrl'       => 'https://x.tk/payu/respuesta.php',
            'confirmationUrl'   => 'http://x.tk/payu/confirmacion.php',
            'confirmacionEmail' => Configuration::get('PS_SHOP_EMAIL'),
            'firmaMd5'          => $firmaMd5,
            'params'            => Configuration::get('PS_OS_BANKWIRE'),
        ]);

        $this->setTemplate('module:ps_payu/views/templates/front/page_validation.tpl');

        //Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }
}
