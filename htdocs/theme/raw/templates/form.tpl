{include file="header.tpl"}
{if $pagedescription}
  <p class="lead">{$pagedescription}</p>
{elseif $pagedescriptionhtml}
  {$pagedescriptionhtml|safe}
{/if}
<div class="panel panel-default panel-body">
    {$form|safe}
</div>
{include file="footer.tpl"}