{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>
<div>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
{if $admin}
<div class="fr">
<span class="addicon">
<a href="{$WWWROOT}interaction/edit.php?group={$groupid|escape}&amp;plugin=forum">{str tag="newforum" section=interaction.forum}</a>
</span>
</div>
{/if}
<br>
{str tag="groupadminlist" section="interaction.forum"}
{foreach name=groupadmins from=$groupadmins item=groupadmin}<a href="{$WWWROOT}user/view.php?id={$groupadmin}" class="groupadmin">
<img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$groupadmin}" alt="">
{$groupadmin|display_name|escape}</a>{if !$smarty.foreach.groupadmins.last}, {/if}{/foreach}
</div>
{if $forums}
<table id="forumslist">
	<tr>
		<th>Forum Name</th>
		<th>Description</th>
		<th>Topics</th>
		<th></th>
		<th></th>
	</tr>
    {foreach from=$forums item=forum}
    <tr class="r{cycle values=0,1}">
        <td class="nowrap"><h4><a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id|escape}">{$forum->title|escape}</a></h4>
        </td>
		<td>
        {$forum->description}
        {if $forum->moderators}
        {str tag="moderatorslist" section="interaction.forum"}
        {foreach from=$forum->moderators item=mod name=moderators}
            <a href="{$WWWROOT}user/view.php?id={$mod}" class="moderator">
            <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$mod}" alt="">
            {$mod|display_name|escape}</a>{if !$smarty.foreach.moderators.last}, {/if}
        {/foreach}
        {/if}
		</td>
        <td align="center">{$forum->count}</td>
        {if $admin}
        <td class="nowrap">
        <a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}&amp;returnto=index" id="btn-edit">{str tag=edit}</a>
        <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}&amp;returnto=index" id="btn-delete">{str tag=delete}</a>
        </td>
        {/if}
        <td>{$forum->subscribe}</td>
	</tr>
    {/foreach}
</table>
{else}
<div class="message">{str tag=noforums section=interaction.forum}</div>
{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
