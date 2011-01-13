<ul class="groupuserstatus">
{if $group->membershiptype == 'member'}
	<li class="member">
            {str tag="youaregroup$group->role" section="group"}
        </li>
{if $group->canleave}
    <li class="leavegroup"><a href ="{$WWWROOT}group/leave.php?id={$group->id}&amp;returnto={$returnto}" class="btn-leavegroup"><span class="icon">{str tag="leavegroup" section="group"}</span></a></li>
{/if}
{elseif $group->membershiptype == 'admin'}
	<li class="admincontrol"><a href="{$WWWROOT}group/edit.php?id={$group->id}" title="{str tag=edit}" class="btn-editgroup"><span class="icon">{str tag=edit}</span></a>
	<a href="{$WWWROOT}group/delete.php?id={$group->id}" title="{str tag=delete}" class="btn-deletegroup"><span class="icon">{str tag=delete}</span></a></li>
	
{if $group->jointype == 'request' && $group->requests}
	<li class="requestspending">
		<a href="{$WWWROOT}group/members.php?id={$group->id}&amp;membershiptype=request" class="btn-membershiprequests"><span class="icon">{str tag="membershiprequests" section="group"} ({$group->requests})</span></a>
	</li>
{/if}
	
{elseif $group->membershiptype == 'invite'}
	<li class="invite">
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
	<li class="requestedtojoin">{str tag="requestedtojoin" section="group"}</li>
	
{elseif $group->jointype == 'open'}
	<li class="jointhisgroup">{$group->groupjoin|safe}</li>
	
{elseif $group->jointype == 'request'}
	<li class="requesttojoin"><a href="{$WWWROOT}group/requestjoin.php?id={$group->id}&amp;returnto={$returnto}" class="btn-request"><span class="icon">{str tag="requestjoingroup" section="group"}</span></a></li>
{elseif $group->jointype == 'controlled' || $group->jointype == 'invite'}
	<li class="controlled">{str tag="membershiptype.$group->jointype" section="group"}</li>

{/if}
</ul>
