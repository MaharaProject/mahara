{if $group->membershiptype == 'member'}
<div class="btn-action-list">
    <div class="groupuserstatus text-right btn-top-right btn-group btn-group-top">
        {if $group->canleave}
            <a href ="{$WWWROOT}group/leave.php?id={$group->id}&amp;returnto={$returnto}" class="btn btn-default">
                <span class="fa fa-long-arrow-right fa-lg text-danger "></span> 
                <span class="btn-title pls">{str tag="leavegroup" section="group"}</span>
            </a>
        {/if}
        {if $group->invitefriends}
            <a href ="{$WWWROOT}group/inviteusers.php?id={$group->id}&friends=1" class="btn btn-default">
                <span class="fa fa-user-plus fa-lg text-success "></span> 
                <span class="btn-title pls">{str tag="invitefriends" section="group"}</span>
            </a>
        {elseif $group->suggestfriends && ($group->request || $group->jointype == 'open')}
            <a href ="{$WWWROOT}group/suggest.php?id={$group->id}" class="btn btn-default">
                <span class="fa fa-lightbulb-o fa-lg text-success "></span> 
                <span class="btn-title pls">{str tag="suggesttofriends" section="group"}</span>
            </a>
        {/if}
    </div>
</div>

{elseif $group->membershiptype == 'admin'}
    <div class="btn-action-list">
        <div class="groupuserstatus text-right btn-top-right btn-group btn-group-top">
            {if $viewid}
            <a href="{$WWWROOT}view/blocks.php?id={$viewid}&{$group->id}" title="{str tag=editcontentandlayout section='view'}" class="btn btn-default">
                <span class="fa fa-pencil fa-lg text-default"></span>
                <span class="btn-title pls hide-small">{str tag=editthisview section='view'}</span>
            </a>
            {/if}

            <a href="{$WWWROOT}group/edit.php?id={$group->id}" title="{str(tag=editspecific arg1=$group->name)|escape:html|safe} {str tag=settings}" class="btn btn-default">
                <span class="fa fa-cog fa-lg text-default"></span>
                 <span class="btn-title pls hide-small">{str tag=settings}</span>
            </a>
            <a href="{$WWWROOT}group/delete.php?id={$group->id}" title="{str(tag=deletespecific arg1=$group->name)|escape:html|safe}" class="btn btn-default">
                <span class="fa fa-trash fa-lg text-danger"></span> 
               <span class="btn-title pls hide-small">{str tag=delete}</span>
            </a>
        </div>
    </div>

    {if $group->requests}
        <a href="{$WWWROOT}group/members.php?id={$group->id}&amp;membershiptype=request" class="text-small with-heading">
            <span class="label label-warning">{$group->requests}</span>
            <span class="btn-title pls text-warning">
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
            {str tag="grouphaveinvitewithrole" section="group"}: {str tag="$grouprole" section="grouptype.$grouptype"}
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
    <div class="requestedtojoin metadata with-heading">
        {str tag="requestedtojoin" section="group"}
    </div>
{elseif $group->request}
    <div class="btn-action-list requestedtojoin">
        <div class="groupuserstatus text-right btn-top-right btn-group btn-group-top">
            <a href="{$WWWROOT}group/requestjoin.php?id={$group->id}&amp;returnto={$returnto}" class="btn btn-default">
                <span class="fa fa-lg fa-comments text-success prs"></span>
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
    <div class="metadata with-heading">
        {if $group->role == 'member' || $group->role == 'admin'}
            {str tag="youaregroup$group->role" section="group"}
        {else}
            {str tag="youaregroup$group->role" section="grouptype.$group->grouptype"}
        {/if}
    </div>
{/if}