{foreach from=$r.email item=e}
    {if (count($r.email) > 1 && $e->duplicated)}
  <div>{$e->title} *</div>
    {else}
  <div>{$e->title}</div>
    {/if}
{/foreach}
