{include file="header.tpl"}
{if $pagedescription}
  <p class="lead">{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
{if $ADMIN}<div class="panel panel-default panel-body">{/if}

    {$form|safe}

{if $ADMIN}</div>{/if}
{include file="footer.tpl"}