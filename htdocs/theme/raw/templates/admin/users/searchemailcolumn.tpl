{if (count($r.email) == 0)}
<div class="error">
    {str tag='noemailfound' section='admin'}
</div>
{else}
    {if $r.email[0]->primary}
    <div>
        {$r.email[0]->title}{if (count($r.email) > 1 && $r.email[0]->duplicated)} *{/if}
    </div>
    {/if}
    {if (count($r.email) > 1)}
    <div>
      (
        {foreach from=$r.email item=e name=addr}
          {if !$dwoo.foreach.addr.first}{$e->title}{if (count($r.email) > 1 && $e->duplicated)} *{/if}
          {if !$dwoo.foreach.addr.last}, {/if}
        {/foreach}
      )
    </div>
    {/if}
{/if}
