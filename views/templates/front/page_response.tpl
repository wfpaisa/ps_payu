{extends "$layout"}

{block name="content"}
  <section>
   
    <ul>
      {foreach from=$params key=name item=value}
        <li><b>{$name}</b>: {$value}</li>
      {/foreach}
    </ul>
   
  </section>
{/block}
