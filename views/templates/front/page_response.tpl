{extends "$layout"}

{block name="content"}
  <section class="container">
  	<div class="row">
  		<div class="col">

			{if $valida}
				<h1 class="hed-tit">{l s="Summary of the transaction" mod='ps_payu'}</h1>
					<br>
				<ul>
					<li><b>{l s="Status:" mod='ps_payu'}</b> {$estadoTx}</li>
					<li><b>{l s="Order:" mod='ps_payu'}</b> {$referenceCode}</li>
					<br>
					<li><b>{l s="Transaction ID:" mod='ps_payu'}</b> {$transactionId}</li>
					<li><b>{l s="Sales reference:" mod='ps_payu'}</b> {$reference_pol}</li>
				{if $pseBank != ''} 
					<li><b>{l s="Cus:" mod='ps_payu'}</b> {$cus}</li>
					<li><b>{l s="Bank:" mod='ps_payu'}</b> {$pseBank}</li>
				{/if}
					<li><b>{l s="Total:" mod='ps_payu'}</b> ${$total}</li>
					<li><b>{l s="Currency:" mod='ps_payu'}</b> {$order_currency}</li>
					<li><b>{l s="Description:" mod='ps_payu'}</b> {($extra1)}</li>
					<li><b>{l s="Entity:" mod='ps_payu'}</b> {($lapPaymentMethod)}</li>
				</ul>
				<p>
					<a href="{$urls.pages.my_account}" class="btn btn-primary">{l s="View your account" mod='ps_payu'}</a>
				</p>
			{else}
				<h1 class="hed-tit">{l s="Error validating digital signature" mod='ps_payu'}</h2>
				<p>
					<a href="{$urls.pages.contact}">{l s="Please contact us here" mod='ps_payu'}</a>
					<br>
					{if $referenceCode}{l s="Order #" mod='ps_payu'}{$referenceCode}{/if}
				</p>
			{/if}	

  		</div>
  	</div>

  </section>
{/block}
