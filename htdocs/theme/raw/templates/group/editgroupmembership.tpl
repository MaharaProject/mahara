<div id="overlay"></div>
<div id="groupboxwrap">
<div class="fr"><a href="" onclick="addElementClass('groupbox', 'hidden');return false;"><img class="close" src="{theme_image_url filename='btn_close'}"></a></div>
<h3>{str tag=editmembershipforuser section=group arg1=display_name($userid)}</h3>
{if !$data}
<p>{str tag=nogroups section=group}</p>
{else}
<div class="fullwidth center">
  {foreach from=$data key=addtype item=groups}
    {if $groups}
<div class="fl jointype">
  <h3>{if $addtype == 'add'}{str tag=addmembers section=group}{else}{str tag=invite section=group}{/if}</h3>
  <ul>
      {foreach from=$groups item=group}
    <li>
      <input type="checkbox" class="checkbox" name="{$addtype}group_{$userid}" value="{$group->id}"{if $group->checked} checked{/if}{if $group->disabled} disabled{/if}> {$group->name} 
    </li>
      {/foreach}
    <li class="last"><a class="btn" href="" onclick="changemembership(event, {$userid}, '{$addtype}');">{str tag=applychanges}</a></li>
    {/if}
  </ul>
</div>
  {/foreach}
</div>
{/if}
<div class="cb"></div>
</div>