<div class="list-group-item {if $group->membershiptype == 'invite' || $group->membershiptype == 'request' || $group->requests} list-group-item-warning{/if}">
    <a href="{$group->homeurl}" class="outer-link"><span class="sr-only">{$group->name}</span></a>
    <div class="row">
        <div class="col-md-8">
            <h3 class="list-group-item-heading text-inline">
                {$group->name}
            </h3>
            {if $group->settingsdescription}
            <span class="text-midtone">
                - {$group->settingsdescription}
            </span>
            {/if}

            <p class="groupdesc">
                {$group->description|str_shorten_html:100:true:true:false|safe}
            </p>
            <div class="groupsdetails text-small">
                 {if $group->editwindow}
                <div class="groupeditable">
                    {str tag=editable section=group}:
                    {$group->editwindow}
                </div>
                {/if}
                {if $group->admins}
                <div class="groupadmin">
                    <strong>{str tag=groupadmins section=group}:</strong>
                    {foreach name=admins from=$group->admins item=user}
                        <a href="{profile_url($user)}" class="inner-link"> {$user|display_name}</a>
                    {if !$.foreach.admins.last},
                    {/if}{/foreach}
                </div>
                {/if}
                {if $group->membercount}
                    <div class="membernumber">
                        <a href="{$WWWROOT}group/members.php?id={$group->id}" class="inner-link">
                            {str tag=Members section=group}: {$group->membercount}
                        </a>
                    </div>
                {/if}
            </div>
        </div>

        <div class="col-md-4">
            <div class="inner-link text-right">
                {include file="group/groupuserstatus.tpl" group=$group}
            </div>
        </div>
    </div>
</div>
