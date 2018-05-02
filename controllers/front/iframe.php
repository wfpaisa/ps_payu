<?php
class Ps_PayUIframeModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign([
            'src' => 'http://www.prestashop.com',
        ]);

        $this->setTemplate('module:paymentexample/views/templates/front/iframe.tpl');
    }
}
