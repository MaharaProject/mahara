{include file="header.tpl"}
{if $subheading}
  <h2>{$subheading}</h2>
{/if}
{if $pagedescription}
  <p>{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
{$form|safe}
{include file="footer.tpl"}

