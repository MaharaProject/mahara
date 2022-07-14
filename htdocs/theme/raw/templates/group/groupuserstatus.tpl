{if $group->membershiptype == 'member'}

{elseif $group->membershiptype == 'admin'}
    {if $group->requests}
        <div class="text-small groupuserstatus">
            <a href="{$WWWROOT}group/members.php?id={$group->id}&amp;membershiptype=request">
                <span class="badge bg-warning">{$group->requests}</span>
                <span class="text-warning">
                    {str tag="membershiprequests" section="group"}
                </span>
            </a>
        </div>
    {/if}

{elseif $group->membershiptype == 'invite'}
    {if $group->role}
        <div class="text-small groupuserstatus">
            {assign var=grouptype value=$group->grouptype}
            {assign var=grouprole value=$group->role}
            {str tag="grouphaveinvitewithrole" section="group"} "{str tag="$grouprole" section="grouptype.$grouptype"}"
        </div>
    {else}
        <div class="text-small groupuserstatus">
            {str tag="grouphaveinvite" section="group"}
        </div>
    {/if}

{elseif $group->jointype == 'open'}

{elseif $group->membershiptype == 'request'}
    <div class="requestedtojoin text-small groupuserstatus">
        {str tag="requestedtojoin" section="group"}
    </div>
{elseif $group->request}

{elseif $group->jointype == 'controlled'}
    <div class="controlled text-small groupuserstatus">
        {str tag="membershipcontrolled" section="group"}
    </div>
{else}
    <div class="controlled text-small groupuserstatus">
        {str tag="membershipbyinvitationonly" section="group"}
    </div>
{/if}

{if $group->membershiptype == 'member'}
    <div class="text-small groupuserstatus">
        {if $group->role == 'member' || $group->role == 'admin'}
            {str tag="youaregroup$group->role" section="group"}
        {else}
            {str tag="youaregroup$group->role" section="grouptype.$group->grouptype"}
        {/if}
    </div>
{/if}
