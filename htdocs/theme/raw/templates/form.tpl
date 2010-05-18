{include file="header.tpl"}
{if $pagedescription}
  <p>{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
{$form|safe}
{include file="footer.tpl"}