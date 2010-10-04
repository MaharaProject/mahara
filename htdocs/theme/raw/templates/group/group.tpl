<h3><a href="{$WWWROOT}group/view.php?id={$group->id}">{$group->name}</a></h3>
<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/view.php?id={$id}">{$id|display_name|escape}</a>{if !$.foreach.admins.last}, {/if}{/foreach}</h6>
<div>{$group->settingsdescription}</div>
{$group->description|str_shorten_html:100:true|safe}
<div class="btn-group s"><label>{str tag="memberslist" section="group"}</label>
{foreach name=members from=$group->members item=member}
	<a href="{$WWWROOT}user/view.php?id={$member->id}">{$member->name}</a>{if !$.foreach.members.last}, {/if}
{/foreach}
{if $group->membercount > 3}<a href="{$WWWROOT}group/members.php?id={$group->id}">...</a>{/if}
</div>
