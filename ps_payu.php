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
        if (
            !parent::install() ||
            !$this->registerHook('paymentOptions') ||
            !$this->registerHook('paymentReturn') ||
            !$this->registerHook('displayHeader') ||
            !Configuration::updateValue('PS_PAYU_SAND_BOX', 0) ||
            !Configuration::updateValue('PS_PAYU_TEST_MODE', 0) ||
            !Configuration::updateValue('PS_PAYU_PAYMENT_STATUS_APPROVED', 2) ||
            !Configuration::updateValue('PS_PAYU_PAYMENT_STATUS_REJECTED', 6) ||
            !Configuration::updateValue('PS_PAYU_PAYMENT_STATUS_PENDING', 3)
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('PS_PAYU_SAND_BOX')||
            !Configuration::deleteByName('PS_PAYU_API_KEY')||
            !Configuration::deleteByName('PS_PAYU_MERCHANT_ID')||
            !Configuration::deleteByName('PS_PAYU_ACCOUNT_ID')||
            !Configuration::deleteByName('PS_PAYU_TEST_MODE')||
            !Configuration::deleteByName('PS_PAYU_PAYMENT_STATUS_APPROVED')||
            !Configuration::deleteByName('PS_PAYU_PAYMENT_STATUS_REJECTED')||
            !Configuration::deleteByName('PS_PAYU_PAYMENT_STATUS_PENDING')
        ) {
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
                       ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/payment.png'));
                       // ->setLogo();

        return $externalOption;
    }

    public function hookdisplayHeader($params)
    {
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->registerStylesheet('modules-ps_payu', 'modules/'.$this->name.'/css/payu.css', ['media' => 'all', 'priority' => 200]);
            $this->context->controller->registerJavascript('modules-ps_payu', 'modules/'.$this->name.'/js/payu.js', ['position' => 'bottom', 'priority' => 200]);
        }
    }

    // Save data
    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name))
        {

            if (
                !Configuration::updateValue('PS_PAYU_SAND_BOX', (int)Tools::getValue('PS_PAYU_SAND_BOX')) ||
                !Configuration::updateValue('PS_PAYU_API_KEY', (string)Tools::getValue('PS_PAYU_API_KEY')) ||
                !Configuration::updateValue('PS_PAYU_MERCHANT_ID', (string)Tools::getValue('PS_PAYU_MERCHANT_ID')) ||
                !Configuration::updateValue('PS_PAYU_ACCOUNT_ID', (string)Tools::getValue('PS_PAYU_ACCOUNT_ID')) ||
                !Configuration::updateValue('PS_PAYU_TEST_MODE', (int)Tools::getValue('PS_PAYU_TEST_MODE')) ||
                !Configuration::updateValue('PS_PAYU_PAYMENT_STATUS_APPROVED', (int)Tools::getValue('PS_PAYU_PAYMENT_STATUS_APPROVED')) ||
                !Configuration::updateValue('PS_PAYU_PAYMENT_STATUS_REJECTED', (int)Tools::getValue('PS_PAYU_PAYMENT_STATUS_REJECTED')) ||
                !Configuration::updateValue('PS_PAYU_PAYMENT_STATUS_PENDING', (int)Tools::getValue('PS_PAYU_PAYMENT_STATUS_PENDING'))
            ) {
                $errors[] = $this->l('Can not save configuration');
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $output .= $this->displayError($error);
                }
            } else {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }

        }

        return $output.$this->displayForm();
    }


    public function displayForm()
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
     
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input' => array(
            
                array(
                    'type' => 'html',
                    'name' => 'PS_PAYU_IMAGE',
                    'label' => $this->l('Example'),
                    'html_content' => '<img style="max-width:100%; border: 1px solid #eee; margin-bottom:15px;" src="'.Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/screenshot.png').'">'
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('PS_PAYU_API_KEY'),
                    'name' => 'PS_PAYU_API_KEY',
                    'desc' => $this->l('See picture point 3'),
                    'size' => 28,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('PS_PAYU_MERCHANT_ID'),
                    'name' => 'PS_PAYU_MERCHANT_ID',
                    'desc' => $this->l('See picture point 1'),
                    'size' => 28,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('PS_PAYU_ACCOUNT_ID'),
                    'desc' => $this->l('See picture point 2'),
                    'name' => 'PS_PAYU_ACCOUNT_ID',
                    'size' => 28,
                    'required' => true
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('Test'),
                    'desc' => $this->l('Must be disabled if PayU configuration is in test mode'),
                    'name' => 'PS_PAYU_TEST_MODE',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),

                array(
                    'type' => 'switch',
                    'label' => $this->l('PS_PAYU_SAND_BOX'),
                    'desc' => $this->l('Disabled in (Test mode)'),
                    'name' => 'PS_PAYU_SAND_BOX',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                ),

                array(
                    'type' => 'text',
                    'label' => $this->l('PS_PAYU_PAYMENT_STATUS_APPROVED'),
                    'name' => 'PS_PAYU_PAYMENT_STATUS_APPROVED',
                    'desc' => $this->l('Only numbers - Order state Id - See(/index.php?controller=AdminStatuses)'),
                    'size' => 2,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('PS_PAYU_PAYMENT_STATUS_REJECTED'),
                    'name' => 'PS_PAYU_PAYMENT_STATUS_REJECTED',
                    'desc' => $this->l('Only numbers - Order state Id - See(/index.php?controller=AdminStatuses)'),
                    'size' => 2,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('PS_PAYU_PAYMENT_STATUS_PENDING'),
                    'name' => 'PS_PAYU_PAYMENT_STATUS_PENDING',
                    'desc' => $this->l('Only numbers - Order state Id - See(/index.php?controller=AdminStatuses)'),
                    'size' => 2,
                    'required' => true
                ),

                array(
                    'type' => 'html',
                    'name' => 'PS_PAYU_IMAGE',
                    'label' => $this->l('Secure configuration'),
                    'html_content' => '<img style="max-width:100%; border: 1px solid #eee; margin-bottom:15px;" src="'.Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/img/secscreenshot.png').'">'
                ),


            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );
     
        $helper = new HelperForm();
     
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
     
        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
     
        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );
     
        // Load current value
        $helper->fields_value['PS_PAYU_SAND_BOX'] =                 Configuration::get('PS_PAYU_SAND_BOX');
        $helper->fields_value['PS_PAYU_API_KEY'] =                  Configuration::get('PS_PAYU_API_KEY');
        $helper->fields_value['PS_PAYU_MERCHANT_ID'] =              Configuration::get('PS_PAYU_MERCHANT_ID');
        $helper->fields_value['PS_PAYU_ACCOUNT_ID'] =               Configuration::get('PS_PAYU_ACCOUNT_ID');
        $helper->fields_value['PS_PAYU_TEST_MODE'] =                Configuration::get('PS_PAYU_TEST_MODE');
        $helper->fields_value['PS_PAYU_PAYMENT_STATUS_APPROVED'] =  Configuration::get('PS_PAYU_PAYMENT_STATUS_APPROVED');
        $helper->fields_value['PS_PAYU_PAYMENT_STATUS_REJECTED'] =  Configuration::get('PS_PAYU_PAYMENT_STATUS_REJECTED');
        $helper->fields_value['PS_PAYU_PAYMENT_STATUS_PENDING'] =   Configuration::get('PS_PAYU_PAYMENT_STATUS_PENDING');

        return $helper->generateForm($fields_form);
    }


}
