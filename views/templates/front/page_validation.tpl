{extends "$layout"}

{block name="content"}
  <section>
	<pre>
		{$params|@print_r}
	</pre>
	<form method="post" action="{$url}">
	  <input name="merchantId"    	value="{$merchantId}" ><br>
	  <input name="accountId"     	value="{$accountId}" ><br>
	  <input name="description"   	value="{$description}" ><br>
	  <input name="referenceCode" 	value="{$referenceCode}" ><br>
	  <input name="amount"        	value="{$amount}" ><br>
	  <input name="tax"           	value="{$tax}" ><br>
	  <input name="taxReturnBase" 	value="{$taxReturnBase}" ><br>
	  <input name="currency"      	value="{$currencyIso}" ><br>
	  <input name="signature"     	value="{$firmaMd5}" ><br>
	  <input name="test"          	value="{$test}" ><br>
	  <input name="buyerEmail"    	value="{$buyerEmail}" ><br>
	  <input name="responseUrl"    	value="{$responseUrl}" ><br>
	  <input name="confirmationUrl" value="{$confirmationUrl}" ><br>
	  <input name="lng"				value="es"><br>
	  <input name="Submit" type="submit" value="Enviar" >
	</form>
  </section>
{/block}
