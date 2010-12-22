<ul class="groupuserstatus">
{if $group->membershiptype == 'member'}
	<li class="member">
            {str tag="youaregroup$group->role" section="group"}
        </li>
{if $group->canleave}
    <li><a href ="{$WWWROOT}group/leave.php?id={$group->id}&amp;returnto={$returnto}" class="btn-leavegroup">{str tag="leavegroup" section="group"}</a></li>
{/if}
{elseif $group->membershiptype == 'admin'}
	<li class="admincontrol"><a href="{$WWWROOT}group/edit.php?id={$group->id}" class="btn-big-edit" title="{str tag=edit}"></a>
	<a href="{$WWWROOT}group/delete.php?id={$group->id}" class="btn-big-del" title="{str tag=delete}"></a></li>
	
{if $group->jointype == 'request' && $group->requests}
	<li>
		<a href="{$WWWROOT}group/members.php?id={$group->id}&amp;membershiptype=request" class="btn-pending">{str tag="membershiprequests" section="group"} ({$group->requests})</a>
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
	{$group->invite|safe}
	</li>
	
{elseif $group->membershiptype == 'request'}
	<li>{str tag="requestedtojoin" section="group"}</li>
	
{elseif $group->jointype == 'open'}
	<li class="jointhisgroup">{$group->groupjoin|safe}</li>
	
{elseif $group->jointype == 'request'}
	<li><a href="{$WWWROOT}group/requestjoin.php?id={$group->id}&amp;returnto={$returnto}" class="btn-request">{str tag="requestjoingroup" section="group"}</a></li>
{elseif $group->jointype == 'controlled' || $group->jointype == 'invite'}
	<li>{str tag="membershiptype.$group->jointype" section="group"}</li>

{/if}
</ul>
