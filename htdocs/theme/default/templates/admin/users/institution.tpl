{if empty($r.institutions)}
  {$institutions.mahara->displayname}
{else}
  {foreach from=$r.institutions item=i}
    {$institutions[$i]->displayname}
  {/foreach}
{/if}