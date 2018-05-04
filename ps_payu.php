<?php

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_PayU extends PaymentModule
{
    protected $_html = '';
    protected $_postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'ps_payu';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Wfpaisa';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('PayU');
        $this->description = $this->l('Payment Webcheckout with PayU Latam');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('paymentOptions') || !$this->registerHook('paymentReturn') || !$this->registerHook('displayHeader')) {
            return false;
        }
        return true;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getExternalPaymentOption(),
        ];

        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getExternalPaymentOption()
    {
    

        $externalOption = new PaymentOption();
        $externalOption->setCallToActionText($this->l('Online payment by credit card, debit card'))
                       ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true)) 
                       ->setAdditionalInformation($this->context->smarty->fetch('module:ps_payu/views/templates/front/hook_payment_option_detail.tpl'))
                       ->setLogo();

        return $externalOption;
    }

    public function hookdisplayHeader($params)
    {
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->registerStylesheet('modules-ps_payu', 'modules/'.$this->name.'/css/payu.css', ['media' => 'all', 'priority' => 200]);
            $this->context->controller->registerJavascript('modules-ps_payu', 'modules/'.$this->name.'/js/payu.js', ['position' => 'bottom', 'priority' => 200]);
        }
    }
}
