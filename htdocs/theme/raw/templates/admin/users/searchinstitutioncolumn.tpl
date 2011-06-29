{if !$r.institutions}
  {$institutions.mahara->displayname}
{else}
  {foreach from=$r.institutions item=i}
  <div>{$institutions[$i]->displayname}</div>
  {/foreach}
{/if}
