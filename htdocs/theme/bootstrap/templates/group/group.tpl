<div class="list-group-item {if $group->membershiptype == 'invite' || $group->membershiptype == 'request' || $group->requests} list-group-item-warning{/if}">
    <a href="{group_homepage_url($group)}" class="outer-link"><span class="sr-only">{$group->name}</span></a>
    <div class="row">
        <div class="col-sm-8">
            <h4 class="list-group-item-heading">
                {$group->name}
                {if $group->settingsdescription}
                <span class="metadata">
                    - {$group->settingsdescription}
                </span>
                {/if}
            </h4>

            <div class="groupsdetails text-small">

                <div class="mtm mbm">
                    {$group->description|str_shorten_html:100:true|safe}
                </div>
                 {if $group->editwindow}
                <div class="groupeditable detail">
                    {str tag=editable section=group}:
                   {$group->editwindow}
                </div>
                {/if}
                <div class="groupadmin detail">
                    {str tag=groupadmins section=group}:
                    {foreach name=admins from=$group->admins item=user}
                    <em>
                        <a href="{profile_url($user)}" class="inner-link"> {$user|display_name}</a>
                    </em>
                    {if !$.foreach.admins.last},
                    {/if}{/foreach}
                </div>
                {if $group->membercount}
                    <div class="membernumber ">
                        <a href="{$WWWROOT}group/members.php?id={$group->id}" class="inner-link">
                            {str tag=Members section=group}: {$group->membercount}
                        </a>
                    </div>
                {/if}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="inner-link text-right">
                {include file="group/groupuserstatus.tpl" group=$group returnto='find'}
            </div>
        </div>
    </div>
</div>
