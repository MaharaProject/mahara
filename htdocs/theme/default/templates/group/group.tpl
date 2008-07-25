<h3><a href="{$WWWROOT}group/view.php?id={$group->id|escape}">{$group->name|escape}</a></h3>
<h6>{foreach name=admins from=$group->admins item=id}<a href="{$WWWROOT}user/view.php?id={$id|escape}">{$id|display_name|escape}</a>{if !$smarty.foreach.admins.last}, {/if}{/foreach}</h6>
{$group->description}
<ul>
<li id="groupmembers">{str tag="memberslist" section="group"}
{if $group->member1}<a href="{$WWWROOT}user/view.php?id={$group->member1|escape}" class="links-members">{$group->member1|display_name|escape}</a>{/if}
{if $group->member2}, <a href="{$WWWROOT}user/view.php?id={$group->member2|escape}" class="links-members">{$group->member2|display_name|escape}</a>{/if}
{if $group->member3}, <a href="{$WWWROOT}user/view.php?id={$group->member3|escape}" class="links-members">{$group->member3|display_name|escape}</a>{/if}
{if $group->membercount > 3}<a href="{$WWWROOT}group/view.php?id={$group->id|escape}&amp;#members" class="links-members">...</a>{/if}
</li>

{include file="group/groupuserstatus.tpl" group=$group returnto='find'}

</ul>
