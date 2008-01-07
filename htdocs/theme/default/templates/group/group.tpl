<h3><a href="{$WWWROOT}group/view.php?id={$group->id|escape}">{$group->name|escape}</a></h3>
<h4><a href="{$WWWROOT}user/view.php?id={$group->owner|escape}">{$group->owner|display_name|escape}</a></h4>
<div>{$group->description}</div>
{str tag="memberslist"}
{if $group->member1}<a href="{$WWWROOT}user/view.php?id={$group->member1|escape}">{$group->member1|display_name|escape}</a>{/if}
{if $group->member2}, <a href="{$WWWROOT}user/view.php?id={$group->member2|escape}">{$group->member2|display_name|escape}</a>{/if}
{if $group->member3}, <a href="{$WWWROOT}user/view.php?id={$group->member3|escape}">{$group->member3|display_name|escape}</a>{/if}
{if $group->membercount > 3}...{/if}
<br>
{if $group->type == 'request'}
{str tag="requestedtojoin"}
{elseif $group->type == 'invite'}
{str tag="grouphaveinvite"}
{$group->invite}
{elseif $group->type == 'owner'}
{str tag="youowngroup"}
{if $group->jointype == 'request' && $group->requests}
    <br>
    {if $group->requests == 1}
        <a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;pending={$group->requests|escape}#members">{str tag=thereispendingrequest}</a>
    {else}
        <a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;pending={$group->requests[0]|escape}#members">{str tag=therearependingrequests args=$group->requests}</a>
    {/if}
{/if}
<br>
<a href="{$WWWROOT}group/edit.php?id={$group->id|escape}">{str tag="edit"}</a>
 | <a href="{$WWWROOT}group/delete.php?id={$group->id|escape}">{str tag="delete"}</a>
{elseif $group->type == 'member'}
{str tag="youaregroupmember"}
{if $group->canleave}
    <br>
    <a href = "{$WWWROOT}group/leave.php?id={$group->id|escape}&amp;returnto={$returnto}">{str tag="leavegroup"}</a>
{/if}
{elseif $group->jointype == 'open'}
{$group->groupjoin}
{elseif $group->jointype == 'request'}
<a href="{$WWWROOT}group/requestjoin.php?id={$group->id|escape}&amp;returnto={$returnto}">{str tag="requestjoingroup"}</a>
{/if}
