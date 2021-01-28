{if $group->membershiptype == 'member'}
    <div class="btn-action-list btn-top-right btn-group btn-group-top">
            {if $list}
            <a href="#" class="label-btn btn btn-secondary" data-id="{$group->id}" title='{str tag="labelfor" section="group" arg1=$group->name}'>
                <span class="icon icon-tag" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="label" section="group"}</span>
            </a>
            {/if}
            {if $group->canleave}
                <a href ="{$WWWROOT}group/leave.php?id={$group->id}" class="btn btn-secondary">
                    <span class="icon icon-long-arrow-alt-right text-danger left" role="presentation" aria-hidden="true"></span>
                    <span class="btn-title">{str tag="leavegroup" section="group"}</span>
                </a>
            {/if}
            {if $group->invitefriends}
                <a href ="{$WWWROOT}group/inviteusers.php?id={$group->id}&friends=1" class="btn btn-secondary">
                    <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span>
                    <span class="btn-title">{str tag="invitefriends" section="group"}</span>
                </a>
            {elseif $group->suggestfriends && ($group->request || $group->jointype == 'open')}
                <a href ="{$WWWROOT}group/suggest.php?id={$group->id}" class="btn btn-secondary">
                    <span class="icon icon-regular icon-lightbulb left" role="presentation" aria-hidden="true"></span>
                    <span class="btn-title">{str tag="suggesttofriends" section="group"}</span>
                </a>
            {/if}
    </div>

{elseif $group->membershiptype == 'admin'}
    <div class="btn-action-list btn-top-right btn-group btn-group-top">
            {if $list}
            <a href="#" class="label-btn btn btn-secondary" data-id="{$group->id}" title='{str tag="labelfor" section="group" arg1=$group->name}'>
                <span class="icon icon-tag" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="label" section="group"}</span>
            </a>
            {/if}
            {if $viewid}
            <a href="{$WWWROOT}view/blocks.php?id={$viewid}&group={$group->id}" title="{str tag=editcontentandlayout section='view'}" class="btn btn-secondary">
                <span class="icon icon-pencil-alt text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=editthisview section='view'}</span>
            </a>
            {/if}

            <a href="{$WWWROOT}group/edit.php?id={$group->id}" title="{str(tag=editspecific arg1=$group->name)|escape:html|safe} {str tag=settings}" class="btn btn-secondary">
                <span class="icon icon-cog text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=settings}</span>
                <span class="sr-only">{str(tag=editspecific arg1=$group->name)|escape:html|safe}</span>
            </a>
            {if $cancopy}
            <a href="{$WWWROOT}group/copy.php?id={$group->id}&return=mygroups" title="{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}" class="btn btn-secondary">
                <span class="icon icon-regular icon-clone text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=copy}</span>
                <span class="sr-only">{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}</span>
            </a>
            {/if}
            <a href="{$WWWROOT}group/delete.php?id={$group->id}" title="{str(tag=deletespecific arg1=$group->name)|escape:html|safe}" class="btn btn-secondary">
                <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=delete}</span>
                <span class="sr-only">{str(tag=deletespecific arg1=$group->name)|escape:html|safe}</span>
            </a>
    </div>

{elseif $group->membershiptype == 'invite'}
    <div class="invite-group btn-action-list btn-top-right btn-group btn-group-top">
        {$group->invite|safe}
    </div>

{elseif $group->jointype == 'open'}

    <div class="join-group btn-action-list btn-top-right btn-group btn-group-top">
        {$group->groupjoin|safe}
    </div>

{elseif $group->request}
    <div class="requestedtojoin btn-action-list btn-top-right btn-group btn-group-top">
            <a href="{$WWWROOT}group/requestjoin.php?id={$group->id}" class="btn btn-secondary">
                <span class="icon icon-comments left" role="presentation" aria-hidden="true"></span>
                {str tag="requestjoingroup" section="group"}
            </a>
    </div>
{/if}
