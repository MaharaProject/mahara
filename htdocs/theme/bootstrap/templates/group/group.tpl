<div class="pull-left">
    <h3 class="title">
        <a href="{group_homepage_url($group)}">
            {$group->name}
        </a>
        {if $group->settingsdescription}
        <span class="groupliststatus metadata">
            - {$group->settingsdescription}
        </span>
        {/if}
    </h3>
    <div class="groupsdetails">
        {if $group->editwindow}
        <div class="groupeditable detail">
            <strong class="prs">{str tag=editable section=group}:</strong> 
            {$group->editwindow}
        </div>
        {/if}
        <div class="groupadmin detail">
            <strong class="prs">{str tag=groupadmins section=group}:</strong> 
            {foreach name=admins from=$group->admins item=user}
            <a href="{profile_url($user)}"> {$user|display_name}</a>
            {if !$.foreach.admins.last}, 
            {/if}{/foreach}
        </div>
        {if $group->membercount}
        <div class="membernumber">
            <a href="{$WWWROOT}group/members.php?id={$group->id}">
                {str tag=nmembers section=group arg1=$group->membercount}
            </a>
        </div>
        {/if}
        <div class="mtm">
            {$group->description|str_shorten_html:100:true|safe}
        </div>
    </div>
</div>
{include file="group/groupuserstatus.tpl" group=$group returnto='find'}
