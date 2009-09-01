{include file="header.tpl"}

<h2>{str tag="nameplural" section=interaction.forum}</h2>
{if $admin}
<div class="rbuttons pagetabs">
<a href="{$WWWROOT}interaction/edit.php?group={$groupid|escape}&amp;plugin=forum" class="btn-add s">{str tag="newforum" section=interaction.forum}</a>
</div>
{/if}
<div class="forummods">
	<label>{str tag="groupadminlist" section="interaction.forum"}</label>
	{foreach from=$groupadmins item=groupadmin}
    <span class="inlinelist">
        <a href="{$WWWROOT}user/view.php?id={$groupadmin}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$groupadmin}" alt=""></a>
        <a href="{$WWWROOT}user/view.php?id={$groupadmin}" class="groupadmin">{$groupadmin|display_name|escape}</a>
    </span>
    {/foreach}
</div>
{if $forums}
<table id="forumslist" class="fullwidth nohead">
	<tr>
		<th>{str tag="name" section="interaction.forum"}</th>
		<th class="center">{str tag="Topics" section="interaction.forum"}</th>
		<th></th>
	</tr>
    {foreach from=$forums item=forum}
    <tr class="r{cycle values=0,1}">
        <td>
            {if $admin}
            <div class="fr btn-spacer s">
                <a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}&amp;returnto=index" id="btn-edit" class="btn-edit">{str tag=edit}</a>
                <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}&amp;returnto=index" class="btn-del">{str tag=delete}</a>
            </div>
            {/if}
            <div class="nowrap">
                <strong><a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id|escape}">{$forum->title|escape}</a></strong>
            </div>
            <div class="s">{$forum->description|str_shorten_html:1000:true}</div>
            {if $forum->moderators}
            <div class="inlinelist">
                <span>{str tag="Moderators" section="interaction.forum"}:</span>
                {foreach from=$forum->moderators item=mod}
                    <a href="{$WWWROOT}user/view.php?id={$mod}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$mod}" alt=""></a>
                    <a href="{$WWWROOT}user/view.php?id={$mod}" class="moderator">{$mod|display_name:null:true|escape}</a>
                {/foreach}
            </div>
            {/if}
        </td>
        <td class="center" width="15%">{$forum->topiccount}</td>
        <td class="nowrap s subscribetd">{if $forum->subscribe}{$forum->subscribe}{/if}</td>
	</tr>
    {/foreach}
</table>
{else}
<div class="message">{str tag=noforums section=interaction.forum}</div>
{/if}
{include file="footer.tpl"}
