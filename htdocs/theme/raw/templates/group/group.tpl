<h3><a href="{$WWWROOT}group/view.php?id={$group->id}">{$group->name}</a></h3>
<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/view.php?id={$id}">{$id|display_name}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
<div class="inline s"> - {$group->settingsdescription}
{$group->description|str_shorten_html:100:true|safe}</div>
{if $group->membercount}
<div class="s"><a href="{$WWWROOT}group/members.php?id={$group->id}">{$group->membercount} {if $group->membercount > 1}{str tag=members section=group}{else}{str tag=member section=group}{/if}</a></div>
{/if}
