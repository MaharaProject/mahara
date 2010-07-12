<h3>{str tag=editmembershipforuser section=group arg1=display_name($userid)}</h3>
{if !$data}
<p>{str tag=nogroups section=group}</p>
{else}
<div class="fullwidth center">
  {foreach from=$data key=type item=groups}
    {if $groups}
<div class="{cycle values='fl,fr'} jointype">
  <div><strong>{str tag=membershiptype.$type section=group}</strong></div>
  <ul>
      {foreach from=$groups item=group}
    <li>
      <input type="checkbox" class="checkbox" name="{$type}group_{$userid}" value="{$group->id}"{if $group->checked} checked{/if}{if $group->disabled} disabled{/if}> {$group->name} 
    </li>
      {/foreach}
    <li class="last"><a class="btn" href="" onclick="changemembership(event, {$userid}, '{$type}');">{str tag=applychanges}</a></li>
    {/if}
  </ul>
</div>
  {/foreach}
</div>
{/if}
<div class="cb"></div>
<p class="fullwidth center"><a class="btn" href="" onclick="addElementClass('groupbox', 'hidden');return false;">{str tag=Close}</a></p>
