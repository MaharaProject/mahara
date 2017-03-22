{if $group->membershiptype == 'member'}
<div class="btn-action-list">
    <div class="groupuserstatus btn-top-right btn-group btn-group-top">
        {if $group->canleave}
            <a href ="{$WWWROOT}group/leave.php?id={$group->id}&amp;returnto={$returnto}" class="btn btn-default">
                <span class="icon icon-long-arrow-right icon-lg text-danger left" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="leavegroup" section="group"}</span>
            </a>
        {/if}
        {if $group->invitefriends}
            <a href ="{$WWWROOT}group/inviteusers.php?id={$group->id}&friends=1" class="btn btn-default">
                <span class="icon icon-user-plus icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="invitefriends" section="group"}</span>
            </a>
        {elseif $group->suggestfriends && ($group->request || $group->jointype == 'open')}
            <a href ="{$WWWROOT}group/suggest.php?id={$group->id}" class="btn btn-default">
                <span class="icon icon-lightbulb-o icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="suggesttofriends" section="group"}</span>
            </a>
        {/if}
    </div>
</div>

{elseif $group->membershiptype == 'admin'}
    <div class="btn-action-list">
        <div class="groupuserstatus btn-top-right btn-group btn-group-top">
            {if $viewid}
            <a href="{$WWWROOT}view/blocks.php?id={$viewid}&group={$group->id}" title="{str tag=editcontentandlayout section='view'}" class="btn btn-default">
                <span class="icon icon-pencil text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=editthisview section='view'}</span>
            </a>
            {/if}

            <a href="{$WWWROOT}group/edit.php?id={$group->id}" title="{str(tag=editspecific arg1=$group->name)|escape:html|safe} {str tag=settings}" class="btn btn-default">
                <span class="icon icon-cog text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=settings}</span>
                <span class="sr-only">{str(tag=editspecific arg1=$group->name)|escape:html|safe}</span>
            </a>
            <a href="{$WWWROOT}group/copy.php?id={$group->id}&return=mygroups" title="{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}" class="btn btn-default">
                <span class="icon icon-clone text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=copy}</span>
                <span class="sr-only">{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}</span>
            </a>
            <a href="{$WWWROOT}group/delete.php?id={$group->id}" title="{str(tag=deletespecific arg1=$group->name)|escape:html|safe}" class="btn btn-default">
                <span class="icon icon-trash text-danger" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=delete}</span>
                <span class="sr-only">{str(tag=deletespecific arg1=$group->name)|escape:html|safe}</span>
            </a>
        </div>
    </div>

    {if $group->requests}
        <a href="{$WWWROOT}group/members.php?id={$group->id}&amp;membershiptype=request" class="text-small">
            <span class="label label-warning">{$group->requests}</span>
            <span class="text-warning">
                {str tag="membershiprequests" section="group"}
            </span>
        </a>
    {/if}

{elseif $group->membershiptype == 'invite'}

    <div class="invite btn-action-list btn-top-right btn-group btn-group-top">
        {$group->invite|safe}
    </div>

    {if $group->role}
        <div class="text-small">
            {assign var=grouptype value=$group->grouptype}
            {assign var=grouprole value=$group->role}
            {str tag="grouphaveinvitewithrole" section="group"} "{str tag="$grouprole" section="grouptype.$grouptype"}"
        </div>
    {else}
        <div class="metadata">
            {str tag="grouphaveinvite" section="group"}
        </div>
    {/if}



{elseif $group->jointype == 'open'}

    <div class="join-group btn-action-list">
        {$group->groupjoin|safe}
    </div>

{elseif $group->membershiptype == 'request'}
    <div class="requestedtojoin text-small">
        {str tag="requestedtojoin" section="group"}
    </div>
{elseif $group->request}
    <div class="btn-action-list requestedtojoin">
        <div class="groupuserstatus btn-top-right btn-group btn-group-top">
            <a href="{$WWWROOT}group/requestjoin.php?id={$group->id}&amp;returnto={$returnto}" class="btn btn-default">
                <span class="icon icon-lg icon-comments left" role="presentation" aria-hidden="true"></span>
                {str tag="requestjoingroup" section="group"}
            </a>
        </div>
    </div>
{elseif $group->jointype == 'controlled'}
    <div class="controlled">
        {str tag="membershipcontrolled" section="group"}
    </div>
{else}
    <div class="controlled">
        {str tag="membershipbyinvitationonly" section="group"}
    </div>
{/if}

{if $group->membershiptype == 'member'}
    <div class="text-small">
        {if $group->role == 'member' || $group->role == 'admin'}
            {str tag="youaregroup$group->role" section="group"}
        {else}
            {str tag="youaregroup$group->role" section="grouptype.$group->grouptype"}
        {/if}
    </div>
{/if}
