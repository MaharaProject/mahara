<h3><a href="{$WWWROOT}group/view.php?id={$group->id|escape}">{$group->name|escape}</a></h3>
<h6><a href="{$WWWROOT}user/view.php?id={$group->owner|escape}">{$group->owner|display_name|escape}</a></h6>
<ul>
{if $group->description}
<li>{$group->description}</li>
{/if}
<li>{str tag="memberslist"}
{if $group->member1}<a href="{$WWWROOT}user/view.php?id={$group->member1|escape}">{$group->member1|display_name|escape}</a>{/if}
{if $group->member2}, <a href="{$WWWROOT}user/view.php?id={$group->member2|escape}">{$group->member2|display_name|escape}</a>{/if}
{if $group->member3}, <a href="{$WWWROOT}user/view.php?id={$group->member3|escape}">{$group->member3|display_name|escape}</a>{/if}
{if $group->membercount > 3}<a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;#members">...</a>{/if}
</li>

{if $group->type == 'member'}
<li>{str tag='youaregroupmember'}</li>
{if $group->canleave}
    <li><a href = "{$WWWROOT}group/leave.php?id={$group->id|escape}&amp;returnto={$returnto}">{str tag="leavegroup"}</a></li>
{/if}

{elseif $group->type == 'owner'}
<li><a href="{$WWWROOT}group/edit.php?id={$group->id|escape}">{str tag="edit"}</a></li>
<li><a href="{$WWWROOT}group/delete.php?id={$group->id|escape}">{str tag="delete"}</a></li>
{if $group->jointype == 'request' && $group->requests}
    <li>
    {if $group->requests == 1}
        <a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;pending={$group->requests|escape}#members">{str tag=thereispendingrequest}</a>
    {else}
        <a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;pending={$group->requests[0]|escape}#members">{str tag=therearependingrequests args=$group->requests}</a>
    {/if}
    </li>
{/if}

{elseif $group->type == 'invite'}
<li>
{str tag="grouphaveinvite"}
{$group->invite}
</li>

{elseif $group->type == 'request'}
<li>{str tag="requestedtojoin"}</li>

{elseif $group->jointype == 'open'}
{$group->groupjoin}

{elseif $group->jointype == 'request'}
<li><a href="{$WWWROOT}group/requestjoin.php?id={$group->id|escape}&amp;returnto={$returnto}">{str tag="requestjoingroup"}</a></li>

{/if}
</ul>