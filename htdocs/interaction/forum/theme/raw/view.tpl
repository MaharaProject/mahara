{include file="header.tpl"}

<h2>{str tag=nameplural section=interaction.forum} &gt; {$subheading}{if $publicgroup}<a href="{$feedlink}"><img class="feedicon" src="{theme_url filename='images/rss.gif'}"></a>{/if}</h2>
<div id="forumbtns" class="rbuttons">
	{if $admin}
		<a href="{$WWWROOT}interaction/edit.php?id={$forum->id}" class="btn editforumtitle">{str tag="edittitle" section="interaction.forum"}</a>
        <a href="{$WWWROOT}interaction/delete.php?id={$forum->id}" class="btn deleteforum">{str tag="deleteforum" section="interaction.forum"}</a>
	{/if}
	{if $membership}{$forum->subscribe|safe}{/if}
</div>
<div id="forumdescription">{$forum->description|clean_html|safe}</div>
<div id="viewforum" class="rel">
	<h3>{str tag=Topics section="interaction.forum"}</h3>
    {if $membership && ($moderator || $forum->newtopicusers != 'moderators') }
    <div class="rbuttons">
	<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id}" class="btn newforumtopic">{str tag="newtopic" section="interaction.forum"}</a>
	</div>
	{/if}
{if $stickytopics || $regulartopics}
<form action="" method="post">
    <table id="forumtopicstable" class="fullwidth">
    <thead>
    <tr>
        <th width="12px"></th>
        <th width="12px"></th>
        <th>{str tag="Topic" section="interaction.forum"}</th>
        <th>{str tag="Poster" section="interaction.forum"}</th>
        <th class="postscount center" width="10%">{str tag="Posts" section="interaction.forum"}</th>
        <th class="lastpost" width="20%">{str tag="lastpost" section="interaction.forum"}</th>
        <th width="20px"></th>
    </tr>
    </thead>
    {if $stickytopics}
	{include file="interaction:forum:topics.tpl" topics=$stickytopics moderator=$moderator forum=$forum publicgroup=$publicgroup sticky=true}
    {/if}
    {if $regulartopics}
	{include file="interaction:forum:topics.tpl" topics=$regulartopics moderator=$moderator forum=$forum publicgroup=$publicgroup sticky=false}
    {/if}
    </table>
    {if $regulartopics}
    	<div class="right">{$pagination|safe}</div>
    {/if}
    {if $membership && (!$forum->subscribed || $moderator)}
    <div class="forumselectwrap"><select name="type">
        <option value="default" selected="selected">{str tag="chooseanaction" section="interaction.forum"}</option>
        {if !$forum->subscribed}
        <option value="subscribe">{str tag="Subscribe" section="interaction.forum"}</option>
        <option value="unsubscribe">{str tag="Unsubscribe" section="interaction.forum"}</option>
        {/if}
        {if $moderator}
        <option value="sticky">{str tag="Sticky" section="interaction.forum"}</option>
        <option value="unsticky">{str tag="Unsticky" section="interaction.forum"}</option>
        <option value="closed">{str tag="Close" section="interaction.forum"}</option>
        <option value="open">{str tag="Open" section="interaction.forum"}</option>
        {/if}
    </select>
    <input type="submit" name="updatetopics" value="{str tag="updateselectedtopics" section="interaction.forum"}" class="submit">
		{if $moderator}
			{contextualhelp plugintype='interaction' pluginname='forum' section='updatemod'}
		{else}
			{contextualhelp plugintype='interaction' pluginname='forum' section='update'}
		{/if}
	</div>	
    {/if}
    <input type="hidden" name="sesskey" value="{$SESSKEY}">
</form>
</div>

<div class="forumfooter">
	<label>{str tag="groupadminlist" section="interaction.forum"}</label>
	{foreach from=$groupadmins item=groupadmin}
    <span class="s inlinelist">
        <a href="{$WWWROOT}user/view.php?id={$groupadmin}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$groupadmin}" alt=""></a>
        <a href="{$WWWROOT}user/view.php?id={$groupadmin}" class="groupadmin">{$groupadmin|display_name}</a>
    </span>
    {/foreach}
	{if $moderators}
    <div>
    <label>{str tag="moderatorslist" section="interaction.forum"}</label>
        {foreach from=$moderators item=mod}
        <span class="s inlinelist">
            <a href="{$WWWROOT}user/view.php?id={$mod}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$mod}" alt=""></a>
            <a href="{$WWWROOT}user/view.php?id={$mod}" class="moderator">{$mod|display_name}</a>
        </span>
        {/foreach}
    </div>
	{/if}
</div>
{else}
<table class="fullwidth nohead">
    <tr><td class="{cycle values='r0,r1'} center">{str tag="notopics" section="interaction.forum"}</td>
    </tr>
</table>
</div>
{/if}

{include file="footer.tpl"}
