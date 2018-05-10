<?php
class Ps_PayUConfirmationModuleFrontController extends ModuleFrontController
{

	public function postProcess()
	{

		if (
			isset($_REQUEST['reference_sale']) &&
			isset($_REQUEST['value']) &&
			isset($_REQUEST['currency']) &&
			isset($_REQUEST['state_pol']) &&
			isset($_REQUEST['sign']) 
		){

			$ApiKey 		= Configuration::get('PS_PAYU_API_KEY');
			$merchantId 	= Configuration::get('PS_PAYU_MERCHANT_ID');
			$referenceCode 	= Db::getInstance()->escape($_REQUEST['reference_sale'], false);
			$txtValue 		= $_REQUEST['value'];
			$newValue 		= number_format($txtValue, 1, '.', '');
			$currency 		= $_REQUEST['currency'];
			$statePol 		= $_REQUEST['state_pol'];
			$sign 			= $_REQUEST['sign'];
			$estadoTxt 		= Configuration::get('PS_PAYU_PAYMENT_STATUS_PENDING');

			$firma = "$ApiKey~$merchantId~$referenceCode~$newValue~$currency~$statePol";
			$firmaMd5 = md5($firma);
			
			if($firmaMd5 === $sign){
				
				switch ($statePol) {
					case 4:
						$estadoTxt = Configuration::get('PS_PAYU_PAYMENT_STATUS_APPROVED');
						break;
					case 6:
						$estadoTxt = Configuration::get('PS_PAYU_PAYMENT_STATUS_REJECTED');
						break;
					case 7:
						$estadoTxt = Configuration::get('PS_PAYU_PAYMENT_STATUS_PENDING');
						break;
					case 104:
						$estadoTxt = 8;
						break;
					default:
						$estadoTxt = Configuration::get('PS_PAYU_PAYMENT_STATUS_PENDING');
				}


			}


			$sql = 'SELECT * FROM '._DB_PREFIX_.'orders  WHERE `reference` LIKE "'.$referenceCode.'"';
			$orderId = Db::getInstance()->getValue($sql);

			if($orderId != false){
				$history = new OrderHistory();
				$history->id_order = (int)$orderId;
				$history->changeIdOrderState( (int)$estadoTxt, (int)($orderId)); 
				// $history->add(true); // No send email
				$history->addWithemail(true); // Send email
			}
			
		}

		
		$this->setTemplate('module:ps_payu/views/templates/front/page_confirmation.tpl');

	}
}
