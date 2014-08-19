<ul class="groupuserstatus">
{if $group->membershiptype == 'member'}
    <li class="member">
    {if $group->role == 'member' || $group->role == 'admin'}
        {str tag="youaregroup$group->role" section="group"}
    {else}
        {str tag="youaregroup$group->role" section="grouptype.$group->grouptype"}
    {/if}
    </li>
    <li class="leavegroup">
    {if $group->canleave}
        <a href ="{$WWWROOT}group/leave.php?id={$group->id}&amp;returnto={$returnto}" class="btn"><span class="btn-leavegroup">{str tag="leavegroup" section="group"}</span></a>
    {/if}
    {if $group->invitefriends}
        <a href ="{$WWWROOT}group/inviteusers.php?id={$group->id}&friends=1" class="btn"><span class="btn-friend">{str tag="invitefriends" section="group"}</span></a>
    {elseif $group->suggestfriends && ($group->request || $group->jointype == 'open')}
        <a href ="{$WWWROOT}group/suggest.php?id={$group->id}" class="btn"><span class="btn-friend">{str tag="suggesttofriends" section="group"}</span></a>
    {/if}
    </li>
{elseif $group->membershiptype == 'admin'}
    <li class="admincontrol">
        <a href="{$WWWROOT}group/edit.php?id={$group->id}" title="{str(tag=editspecific arg1=$group->name)|escape:html|safe}" class="btn">
            <span class="btn-edit">{str tag=editgroup section=group}</span>
            <span class="accessible-hidden">{str tag=editspecific arg1=$group->name}</span>
        </a>
        <a href="{$WWWROOT}group/delete.php?id={$group->id}" title="{str(tag=deletespecific arg1=$group->name)|escape:html|safe}" class="btn">
            <span class="btn-del">{str tag=deletegroup1 section=group}</span>
            <span class="accessible-hidden">{str tag=deletespecific arg1=$group->name}</span>
        </a>
    </li>

    {if $group->requests}
        <li class="requestspending">
            <a href="{$WWWROOT}group/members.php?id={$group->id}&amp;membershiptype=request" class="btn"><span class="btn-pending">{str tag="membershiprequests" section="group"} ({$group->requests})</span></a>
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
{elseif $group->jointype == 'open'}
    <li class="jointhisgroup">{$group->groupjoin|safe}</li>
{elseif $group->membershiptype == 'request'}
    <li class="requestedtojoin">{str tag="requestedtojoin" section="group"}</li>
{elseif $group->request}
    <li class="requesttojoin"><a href="{$WWWROOT}group/requestjoin.php?id={$group->id}&amp;returnto={$returnto}" class="btn"><span class="btn-request">{str tag="requestjoingroup" section="group"}</span></a></li>
{elseif $group->jointype == 'controlled'}
    <li class="controlled">{str tag="membershipcontrolled" section="group"}</li>
{else}
    <li class="controlled">{str tag="membershipbyinvitationonly" section="group"}</li>
{/if}
</ul>
