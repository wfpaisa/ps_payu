<form action="{$action}" id="payment-form">

  <p>
    <label>{l s='Card number'}</label>
    <input type="text" size="20" autocomplete="off" name="card-number">
  </p>

  <p>
    <label>{l s='Firstname'}</label>
    <input type="text" autocomplete="off" name="firstname">
  </p>

  <p>
    <label>{l s='Lastname'}</label>
    <input type="text" autocomplete="off" name="lastname">
  </p>

  <p>
    <label>{l s='CVC'}</label>
    <input type="text" size="4" autocomplete="off" name="card-cvc">
  </p>

  <p>
    <label>{l s='Expiration (MM/AAAA)'}</label>
    <select id="month" name="card-expiry-month">
      {foreach from=$months item=month}
        <option value="{$month}">{$month}</option>
      {/foreach}
    </select>
    <span> / </span>
    <select id="year" name="card-expiry-year">
      {foreach from=$years item=year}
        <option value="{$year}">{$year}</option>
      {/foreach}
    </select>
  </p>
</form>
