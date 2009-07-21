{include file="header.tpl"}

<h2>{str tag="nameplural" section=interaction.forum}</h2>
{if $admin}
<div class="rbuttons pagetabs">
<a href="{$WWWROOT}interaction/edit.php?group={$groupid|escape}&amp;plugin=forum" class="btn-add s">{str tag="newforum" section=interaction.forum}</a>
</div>
{/if}
<div>
	<label>{str tag="groupadminlist" section="interaction.forum"}</label>
	{foreach from=$groupadmins item=groupadmin}
    <span class="s inlinelist">
        <a href="{$WWWROOT}user/view.php?id={$groupadmin}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$groupadmin}" alt=""></a>
        <a href="{$WWWROOT}user/view.php?id={$groupadmin}" class="groupadmin">{$groupadmin|display_name|escape}</a>
    </span>
    {/foreach}
</div>
{if $forums}
<table id="forumslist" class="fullwidth">
	<tr>
		<th>{str tag="forumname" section="interaction.forum"}</th>
		<th>{str tag="description"}</th>
		<th>{str tag=Moderators section=interaction.forum}</th>
		<th>{str tag="Topics" section="interaction.forum"}</th>
		{if $admin}<th></th>{/if}
		{if $forum->subscribe}{/if}<th></th>
	</tr>
    {foreach from=$forums item=forum}
    <tr class="r{cycle values=0,1}">
        <td class="nowrap"><strong><a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id|escape}">{$forum->title|escape}</a></strong>
        </td>
		<td>
        {$forum->description}
        </td>
        <td>
        {if $forum->moderators}
        {foreach from=$forum->moderators item=mod}
            <div class="s">
                <a href="{$WWWROOT}user/view.php?id={$mod}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$mod}" alt=""></a>
                <a href="{$WWWROOT}user/view.php?id={$mod}" class="moderator forumuser">{$mod|display_name|escape}</a>
            </div>
        {/foreach}
        {/if}
		</td>
        <td align="center">{$forum->topiccount}</td>
        {if $admin}
        <td class="nowrap btn-spacer s">
        <a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}&amp;returnto=index" id="btn-edit" class="btn-edit">{str tag=edit}</a>
        <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}&amp;returnto=index" class="btn-del">{str tag=delete}</a>
        </td>
        {/if}
        {if $forum->subscribe}<td class="nowrap s">{$forum->subscribe}</td>{/if}
	</tr>
    {/foreach}
</table>
{else}
<div class="message">{str tag=noforums section=interaction.forum}</div>
{/if}
{include file="footer.tpl"}
