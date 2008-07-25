{if $group->membershiptype == 'member'}
<li>{str tag="youaregroupmember" section="group"}</li>
{if $group->canleave}
    <li><a href = "{$WWWROOT}group/leave.php?id={$group->id|escape}&amp;returnto={$returnto}" id="btn-leavegroup">{str tag="leavegroup" section="group"}</a></li>
{/if}

{elseif $group->membershiptype == 'admin'}
<li><a href="{$WWWROOT}group/edit.php?id={$group->id|escape}" id="btn-edit">{str tag="edit"}</a></li>
<li><a href="{$WWWROOT}group/delete.php?id={$group->id|escape}" id="btn-delete">{str tag="delete"}</a></li>
{if $group->jointype == 'request' && $group->requests}
    <li>
    {if $group->requests == 1}
        <a href="{$WWWROOT}group/members.php?id={$group->id|escape}&amp;membershiptype=request" id="btn-requests">{str tag="thereispendingrequest" section="group"}</a>
    {else}
        <a href="{$WWWROOT}group/members.php?id={$group->id|escape}&amp;membershiptype=request" id="btn-requests">{str tag="therearependingrequests" section="group" args=$group->requests}</a>
    {/if}
    </li>
{/if}

{elseif $group->membershiptype == 'invite'}
<li>
  {if $group->role}
    {assign var=grouptype value=$group->grouptype}
    {assign var=grouprole value=$group->role}
    {str tag="grouphaveinvitewithrole" section="group"}: {str tag="$grouprole" section="grouptype.$grouptype"}
  {else}
    {str tag="grouphaveinvite" section="group"}
  {/if}
{$group->invite}
</li>

{elseif $group->membershiptype == 'request'}
<li>{str tag="requestedtojoin" section="group"}</li>

{elseif $group->jointype == 'open'}
{$group->groupjoin}

{elseif $group->jointype == 'request'}
<li><a href="{$WWWROOT}group/requestjoin.php?id={$group->id|escape}&amp;returnto={$returnto}" id="btn-requests">{str tag="requestjoingroup" section="group"}</a></li>

{/if}
