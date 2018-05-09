{* {extends "$layout"} *}

{block name="content"}

	<form id="payu" method="post" action="{$url}" style="display:none;">
	  <input name="merchantId"    	 type="hidden" value="{$merchantId}" ><br>
	  <input name="accountId"     	 type="hidden" value="{$accountId}" ><br>
	  <input name="description"   	 type="hidden" value="{$description}" ><br>
	  <input name="referenceCode" 	 type="hidden" value="{$referenceCode}" ><br>
	  <input name="amount"        	 type="hidden" value="{$amount}" ><br>
	  <input name="tax"           	 type="hidden" value="{$tax}" ><br>
	  <input name="taxReturnBase" 	 type="hidden" value="{$taxReturnBase}" ><br>
	  <input name="currency"      	 type="hidden" value="{$currencyIso}" ><br>
	  <input name="signature"     	 type="hidden" value="{$firmaMd5}" ><br>
	  <input name="test"          	 type="hidden" value="{$test}" ><br>
	  <input name="buyerEmail"    	 type="hidden" value="{$buyerEmail}" ><br>
	  <input name="responseUrl"    	 type="hidden" value="{$responseUrl}" ><br>
	  <input name="confirmationUrl"  type="hidden" value="{$confirmationUrl}" ><br>
	  <input name="lng"				 type="hidden" value="es"><br>
	  <input name="Submit" type="submit"  type="hidden" value="Enviar" >
	</form>
	<script>
		document.getElementById("payu").submit();
	</script>

	<p style="text-align: center; font-size: 20px; color:#a6c407; font-family: Arial; font-weight: 600; margin: 20px;">
		{* <img src="{$moduleDirUrl}" alt="PayU"> *}
		{l s="PayU..." d='Modules.Ps_payu.shop'}
	</p>
{/block}
