<?php
class Ps_PayUResponseModuleFrontController extends ModuleFrontController
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
			die($this->module->l('This payment method is not available.', 'response'));
		}

		$errors = array();

		if (
			isset($_REQUEST['merchantId']) &&
			isset($_REQUEST['referenceCode']) &&
			isset($_REQUEST['TX_VALUE']) &&
			isset($_REQUEST['currency']) &&
			isset($_REQUEST['transactionState']) &&
			isset($_REQUEST['signature']) &&
			isset($_REQUEST['reference_pol']) &&
			isset($_REQUEST['lapPaymentMethod']) &&
			isset($_REQUEST['transactionId']) &&
			$_REQUEST['transactionState'] != 104
		){

			$ApiKey              = Configuration::get('PS_PAYU_API_KEY');
			$merchant_id         = $_REQUEST['merchantId'];
			$referenceCode       = $_REQUEST['referenceCode'];
			$order_currency      = $_REQUEST['currency'];
			$transactionState    = $_REQUEST['transactionState'];
			$signature           = strtoupper($_REQUEST['signature']);
			$reference_pol       = $_REQUEST['reference_pol'];
			$cus                 = (isset($_REQUEST['cus'])? $_REQUEST['cus']: '');
			$extra1              = (isset($_REQUEST['description'])? $_REQUEST['description']: '');
			$pseBank             = (isset($_REQUEST['pseBank'])? $_REQUEST['pseBank']: '');
			$lapPaymentMethod    = $_REQUEST['lapPaymentMethod'];
			$transactionId       = $_REQUEST['transactionId'];
			$TX_VALUE            = $_REQUEST['TX_VALUE'];


			$TX_VALUE			 = round($TX_VALUE, 1, PHP_ROUND_HALF_EVEN);
			$split 				 = explode('.', $TX_VALUE);
			$decimals 			 = $split[1];

			if ($decimals % 10 == 0){
				$TX_VALUE 	= number_format($TX_VALUE, 1, '.', '');
			}

			$firma               = "$ApiKey~$merchant_id~$referenceCode~$TX_VALUE~$order_currency~$transactionState";
			$firmaMd5            = strtoupper(md5($firma));

			switch ($transactionState) {
				case 4:
					$estadoTx = $this->module->l('Order approved', 'response');
					break;
				case 6:
					$estadoTx = $this->module->l('Order rejected', 'response');
					break;
				case 7:
					$estadoTx = $this->module->l('Order unresolved', 'response');
					break;
				default:
					// $estadoTx = (isset($_REQUEST['message'])? $_REQUEST['message']: '' );
					$estadoTx = $this->module->l('Order unresolved', 'response');
			}

			if($signature != $firmaMd5){
				// $errors[] = $this->module->l('Error validating digital signature', 'response');
			}


		}else{
			$errors[] = $this->module->l('Error validating digital signature', 'response'); 
		}


		if(empty($errors)){
			
			$this->context->smarty->assign([
				'valida'			=> 1,
				'estadoTx'			=> $estadoTx,
				'transactionId' 	=> $transactionId,
				'reference_pol' 	=> $reference_pol,
				'referenceCode' 	=> $referenceCode,
				'cus'				=> $cus,
				'pseBank'			=> $pseBank,
				'total'				=> $TX_VALUE,
				'order_currency'	=> $order_currency,
				'extra1'			=> $extra1,
				'lapPaymentMethod'	=> $lapPaymentMethod,

			]);

			$this->setTemplate('module:ps_payu/views/templates/front/page_response.tpl');

		}else{
			
			$this->context->smarty->assign([
				'valida'		=> 0,
				'errors'		=> $errors,
				'referenceCode' => (isset($_REQUEST['referenceCode'])? $_REQUEST['referenceCode']: ''),
			]);

			$this->setTemplate('module:ps_payu/views/templates/front/page_response.tpl');
		}

		
	}
}
