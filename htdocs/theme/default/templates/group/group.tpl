<h3><a href="{$WWWROOT}group/view.php?id={$group->id|escape}">{$group->name|escape}</a></h3>
<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$smarty.foreach.admins.last}, {/if}{/foreach}</h6>
{$group->description}
<ul>
<li id="groupmembers">{str tag="memberslist" section="group"}
{if $group->member1}<a href="{$WWWROOT}user/view.php?id={$group->member1|escape}" class="links-members">{$group->member1|display_name|escape}</a>{/if}
{if $group->member2}, <a href="{$WWWROOT}user/view.php?id={$group->member2|escape}" class="links-members">{$group->member2|display_name|escape}</a>{/if}
{if $group->member3}, <a href="{$WWWROOT}user/view.php?id={$group->member3|escape}" class="links-members">{$group->member3|display_name|escape}</a>{/if}
{if $group->membercount > 3}<a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;#members" class="links-members">...</a>{/if}
</li>

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
        <a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;pending={$group->requests|escape}#members" id="btn-requests">{str tag="thereispendingrequest" section="group"}</a>
    {else}
        <a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;pending={$group->requests[0]|escape}#members" id="btn-requests">{str tag="therearependingrequests" args=$group->requests}</a>
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
</ul>
