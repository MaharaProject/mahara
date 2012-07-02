<h3><a href="{group_homepage_url($group)}">{$group->name}</a></h3>
<h6>{foreach name=admins from=$group->admins item=user}<a href="{profile_url($user)}">{$user|display_name}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
{if $group->settingsdescription}<div class="inline s"> - {$group->settingsdescription}</div>{/if}
<div class="s">{$group->description|str_shorten_html:100:true|safe}</div>
{if $group->membercount}
<div class="s"><a href="{$WWWROOT}group/members.php?id={$group->id}">{$group->membercount} {if $group->membercount > 1}{str tag=members section=group}{else}{str tag=member section=group}{/if}</a></div>
{/if}
{if $group->editwindow}
<div>{str tag=Available section=group}: {$group->editwindow}</div>
{/if}
