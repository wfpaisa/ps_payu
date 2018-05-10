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
        $order_state_approved   = Configuration::get('PS_PAYU_PAYMENT_STATUS_APPROVED');
        $order_state_rejected   = Configuration::get('PS_PAYU_PAYMENT_STATUS_REJECTED');
        $order_state_pending    = Configuration::get('PS_PAYU_PAYMENT_STATUS_PENDING');
        
        // Active email data
        $mailVars       = array(
            // '{ps_payu_owner}' => Configuration::get('PS_PAYU_OWNER'),
            // '{ps_payu_details}' => nl2br(Configuration::get('PS_PAYU_DETAILS')),
            // '{ps_payu_address}' => nl2br(Configuration::get('PS_PAYU_ADDRESS'))
        );

        // Important this make the order reference
        // 
        // parameters validateOrder(
        //     $id_cart,
        //     $id_order_state,
        //     $amount_paid,
        //     $payment_method = 'Unknown',
        //     $message = null,
        //     $extra_vars = array(),
        //     $currency_special = null,
        //     $dont_touch_amount = false,
        //     $secure_key = false,
        //     Shop $shop = null
        // )
        $this->module->validateOrder($cart->id, $order_state_pending, $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);

        // PayU Data
        $urlProduction          = 'https://checkout.payulatam.com/ppp-web-gateway-payu/';
        $urlSandbox             = 'https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/'; // Sandbox
        $order                  = new Order($this->module->currentOrder); // $this->module->currentOrder is generate after validateOrder
        $order_reference        = ($order->reference ? $order->reference : 0);
        $ApiKey                 = Configuration::get('PS_PAYU_API_KEY');
        $merchantId             = Configuration::get('PS_PAYU_MERCHANT_ID');
        $accountId              = Configuration::get('PS_PAYU_ACCOUNT_ID');
        $url                    = (Configuration::get('PS_PAYU_SAND_BOX') ?  $urlSandbox : $urlProduction);
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
            'test'              => Configuration::get('PS_PAYU_TEST_MODE'),
            'buyerEmail'        => $customer->email,
            'responseUrl'       => $this->context->link->getModuleLink( $this->module->name, 'response'),
            'confirmationUrl'   => $this->context->link->getModuleLink( $this->module->name, 'confirmation'),
            'confirmacionEmail' => Configuration::get('PS_SHOP_EMAIL'),
            'firmaMd5'          => $firmaMd5,
            'moduleDirUrl'      => Media::getMediaPath(_PS_MODULE_DIR_.$this->module->name.'/img/payu_logo.png'),
            'params'            => '',
        ]);

        $this->setTemplate('module:ps_payu/views/templates/front/page_validation.tpl');
        //Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);

    }
}
