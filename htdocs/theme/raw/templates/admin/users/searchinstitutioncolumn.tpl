{if !$r.institutions}
  {$institutions.mahara->displayname}
{else}
  {foreach from=$r.institutions item=i}
  <div>{$institutions[$i]->displayname}</div>
  {/foreach}
{/if}

{if $r.requested}
  {foreach from=$r.requested item=i}
  <div class="pending">{str tag=requestto section=admin} {$institutions[$i]->displayname}
    {if $USER->is_institutional_admin("$i")}
    (<a href="{$WWWROOT}admin/users/addtoinstitution.php?id={$r.id}&institution={$i}">{str tag=confirm section=admin}</a>)
    {/if}
  </div>
  {/foreach}
{/if}

{if $r.invitedby}
  {foreach from=$r.invitedby item=i}
  <div class="pending">{str tag=invitedby section=admin} {$institutions[$i]->displayname}</div>
  {/foreach}
{/if}
