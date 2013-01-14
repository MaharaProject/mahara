<h4><a href="{group_homepage_url($group)}">{$group->name}</a></h4>
{if $group->settingsdescription}<div class="grouplistdescription">{$group->settingsdescription}</div>{/if}
<div class="grouplistdescription">{$group->description|str_shorten_html:100:true|safe}</div>
{if $group->editwindow}
<div class="s">{str tag=editable section=group}: {$group->editwindow}</div>
{/if}
<div class="s">{str tag=groupadmins section=group}: {foreach name=admins from=$group->admins item=user}<a href="{profile_url($user)}">{$user|display_name}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</div>
{if $group->membercount}
<div class="s"><a href="{$WWWROOT}group/members.php?id={$group->id}">{str tag=nmembers section=group arg1=$group->membercount}</a></div>
{/if}
