{include file="header.tpl"}
{if $admin}
<div id="forumbtns">
<a href="{$WWWROOT}interaction/edit.php?group={$groupid}&amp;plugin=forum" class="btn newforum">{str tag="newforum" section=interaction.forum}</a>
</div>
{/if}
<h2>{str tag="nameplural" section=interaction.forum}{if $publicgroup}<a href="{$feedlink}"><img class="feedicon" src="{theme_image_url filename='feed'}"></a>{/if}</h2>
{if $forums}
<div id="viewforum"><table id="forumslist" class="fullwidth nohead">
	<tr>
		<th>{str tag="name" section="interaction.forum"}</th>
		<th class="center">{str tag="Topics" section="interaction.forum"}</th>
		<th class="subscribeth">
            <span class="accessible-hidden">{str tag=Subscribe section=interaction.forum}</span>
        </th>
		<th class="right btns2">
            <span class="accessible-hidden">{str tag=edit}</span>
        </th>
	</tr>
    {foreach from=$forums item=forum}
    <tr class="{cycle values='r0,r1'}">
        <td>
            <h3 class="title"><a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id}">{$forum->title}</a>{if $publicgroup}<a href="{$forum->feedlink}"><img class="feedicon" src="{theme_image_url filename='feed'}"></a>{/if}</h3>
            <div class="detail">{$forum->description|str_shorten_html:1000:true|safe}</div>
            {if $forum->moderators}
            <div class="inlinelist">
                <span>{str tag="Moderators" section="interaction.forum"}:</span>
                {foreach from=$forum->moderators item=mod}
                    <a href="{profile_url($mod)}"><img src="{profile_icon_url user=$mod maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$mod|display_default_name}"></a>
                    <a href="{profile_url($mod)}" class="moderator">{$mod|display_name:null:true}</a>
                {/foreach}
            </div>
            {/if}
        </td>
        <td class="center">{$forum->topiccount}</td>
        <td class="nowrap s subscribetd">{if $forum->subscribe}{$forum->subscribe|safe}{/if}</td>
        <td class="right btns2">
        {if $admin}
            <a href="{$WWWROOT}interaction/edit.php?id={$forum->id}&amp;returnto=index" class="icon btn-big-edit" title="{str tag=edit}">
                {str tag=editspecific arg1=$forum->title}
            </a>
            <a href="{$WWWROOT}interaction/delete.php?id={$forum->id}&amp;returnto=index" class="icon btn-big-del" title="{str tag=delete}">
                {str tag=deletespecific arg1=$forum->title}
            </a>
        {/if}
        </td>
	</tr>
    {/foreach}
</table></div>
{else}
<div class="message">{str tag=noforums section=interaction.forum}</div>
{/if}
<div class="forummods">
	<strong>{str tag="groupadminlist" section="interaction.forum"}</strong>
	{foreach from=$groupadmins item=groupadmin}
    <span class="inlinelist">
        <a href="{profile_url($groupadmin)}" class="groupadmin"><img src="{profile_icon_url user=$groupadmin maxheight=20 maxwidth=20}" alt="{str tag=profileimagetext arg1=$groupadmin|display_default_name}"> {$groupadmin|display_name}</a>
    </span>
    {/foreach}
</div>
{include file="footer.tpl"}
