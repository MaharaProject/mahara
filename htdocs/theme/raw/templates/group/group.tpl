<h3 class="title"><a href="{group_homepage_url($group)}">{$group->name}</a></h3>
{include file="group/groupuserstatus.tpl" group=$group returnto='find'}
<div class="groupsdetails">
    {if $group->settingsdescription}<div class="groupliststatus">{$group->settingsdescription}</div>{/if}
    <div class="detail">{$group->description|str_shorten_html:100:true|safe}</div>
    {if $group->editwindow}
    <div class="groupeditable">{str tag=editable section=group}: {$group->editwindow}</div>
    {/if}
    <div class="groupadmin">{str tag=groupadmins section=group}: {foreach name=admins from=$group->admins item=user}<a href="{profile_url($user)}">{$user|display_name}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</div>
    {if $group->membercount}
    <div class="membernumber"><a href="{$WWWROOT}group/members.php?id={$group->id}">{str tag=nmembers section=group arg1=$group->membercount}</a></div>
    {/if}
</div>
