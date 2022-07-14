{if $group->membershiptype == 'member'}
    <div class="btn-action-list btn-top-right btn-group btn-group-top">
            {if $list}
            <button class="label-btn btn btn-secondary" type="button" data-id="{$group->id}" title='{str tag="labelfor" section="group" arg1=$group->name}'>
                <span class="icon icon-tag" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="label" section="group"}</span>
            </button>
            {/if}
            {if $group->canleave}
                <button data-url="{$WWWROOT}group/leave.php?id={$group->id}" type="button" class="btn btn-secondary">
                    <span class="icon icon-long-arrow-alt-right text-danger left" role="presentation" aria-hidden="true"></span>
                    <span class="btn-title">{str tag="leavegroup" section="group"}</span>
                </button>
            {/if}
            {if $group->invitefriends}
                <button data-url="{$WWWROOT}group/inviteusers.php?id={$group->id}&friends=1" type="button" class="btn btn-secondary">
                    <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span>
                    <span class="btn-title">{str tag="invitefriends" section="group"}</span>
                </button>
            {elseif $group->suggestfriends && ($group->request || $group->jointype == 'open')}
                <button data-url ="{$WWWROOT}group/suggest.php?id={$group->id}" type="button" class="btn btn-secondary">
                    <span class="icon icon-regular icon-lightbulb left" role="presentation" aria-hidden="true"></span>
                    <span class="btn-title">{str tag="suggesttofriends" section="group"}</span>
                </button>
            {/if}
    </div>

{elseif $group->membershiptype == 'admin'}
    <div class="btn-action-list btn-top-right btn-group btn-group-top">
            {if $list}
            <button class="label-btn btn btn-secondary" data-id="{$group->id}" type="button" title='{str tag="labelfor" section="group" arg1=$group->name}'>
                <span class="icon icon-tag" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag="label" section="group"}</span>
            </button>
            {/if}
            {if $viewid}
            <button data-url="{$WWWROOT}view/blocks.php?id={$viewid}&group={$group->id}" title="{str tag=editcontentandlayout section='view'}" type="button" class="btn btn-secondary">
                <span class="icon icon-pencil-alt text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=editthisview section='view'}</span>
            </button>
            {/if}

            <button data-url="{$WWWROOT}group/edit.php?id={$group->id}" title="{str(tag=editspecific arg1=$group->name)|escape:html|safe} {str tag=settings}" type="button" class="btn btn-secondary">
                <span class="icon icon-cog text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=settings}</span>
                <span class="visually-hidden">{str(tag=editspecific arg1=$group->name)|escape:html|safe}</span>
            </button>
            {if $cancopy}
            <button data-url="{$WWWROOT}group/copy.php?id={$group->id}&return=mygroups" title="{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}" type="button" class="btn btn-secondary">
                <span class="icon icon-regular icon-clone text-default" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=copy}</span>
                <span class="visually-hidden">{str(tag=copygroup section=group arg1=$group->name)|escape:html|safe}</span>
            </button>
            {/if}
            <button data-url="{$WWWROOT}group/delete.php?id={$group->id}" title="{str(tag=deletespecific arg1=$group->name)|escape:html|safe}" type="submit" class="btn btn-secondary">
                <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                <span class="btn-title">{str tag=delete}</span>
                <span class="visually-hidden">{str(tag=deletespecific arg1=$group->name)|escape:html|safe}</span>
            </button>
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
            <button data-url="{$WWWROOT}group/requestjoin.php?id={$group->id}" type="button" class="btn btn-secondary">
                <span class="icon icon-comments left" role="presentation" aria-hidden="true"></span>
                {str tag="requestjoingroup" section="group"}
            </button>
    </div>
{/if}
