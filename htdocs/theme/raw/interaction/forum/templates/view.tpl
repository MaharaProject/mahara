{include file="header.tpl"}

<div id="forumbtns">
	{if $admin}
		<a href="{$WWWROOT}interaction/edit.php?id={$forum->id}" class="btn editforumtitle">{str tag="edittitle" section="interaction.forum"}</a>
        <a href="{$WWWROOT}interaction/delete.php?id={$forum->id}" class="btn deleteforum">{str tag="deleteforum" section="interaction.forum"}</a>
	{/if}
	{if $membership}{$forum->subscribe|safe}{/if}
</div>
<h2>{str tag=nameplural section=interaction.forum} &gt; {$subheading}{if $publicgroup}<a href="{$feedlink}"><img class="feedicon" src="{theme_image_url filename='feed'}"></a>{/if}</h2>
<div id="forumdescription">{$forum->description|clean_html|safe}</div>
<div id="viewforum">
	<h3>{str tag=Topics section="interaction.forum"}</h3>
    {if $membership && ($moderator || ($forum->newtopicusers != 'moderators') && $ineditwindow) }
    <div class="rbuttons">
	<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id}" class="btn newforumtopic">{str tag="newtopic" section="interaction.forum"}</a>
	</div>
	{/if}
{if $stickytopics || $regulartopics}
<form action="" method="post">
    <table id="forumtopicstable" class="fullwidth">
    <thead>
    <tr>
        <th class="narrow"></th>
        <th class="narrow"></th>
        <th class="topic">{str tag="Topic" section="interaction.forum"}</th>
        <th class="posterth">{str tag="Poster" section="interaction.forum"}</th>
        <th class="postscount center">{str tag="Posts" section="interaction.forum"}</th>
        <th class="lastpost">{str tag="lastpost" section="interaction.forum"}</th>
        {if $moderator}<th class="right btns2"></th>{/if}
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
    	<div>{$pagination|safe}</div>
    {/if}
    {if $membership && (!$forum->subscribed || $moderator)}
    <div class="forumselectwrap"><select name="type" id="action">
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
        {if $moderator && $otherforums && (count($otherforums) > 0)}
        <option value="moveto">{str tag="Moveto" section="interaction.forum"}</option>
        {/if}
    </select>
    {if $moderator && $otherforums && (count($otherforums) > 0)}
    <select name="newforum" id="otherforums" class="hidden">
        {foreach from=$otherforums item=otherforum}
        <option value="{$otherforum->id}">{$otherforum->title}</option>
        {/foreach}
    </select>
    {/if}
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
	<strong>{str tag="groupadminlist" section="interaction.forum"}</strong>
	{foreach from=$groupadmins item=groupadmin}
    <span class="s inlinelist">
        <a href="{profile_url($groupadmin)}"><img src="{profile_icon_url user=$groupadmin maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$groupadmin|display_default_name}"></a>
        <a href="{profile_url($groupadmin)}" class="groupadmin">{$groupadmin|display_name}</a>
    </span>
    {/foreach}
	{if $moderators}
    <div>
    <strong>{str tag="moderatorslist" section="interaction.forum"}</strong>
        {foreach from=$moderators item=mod}
        <span class="s inlinelist">
            <a href="{profile_url($mod)}"><img src="{profile_icon_url user=$mod maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$mod|display_default_name}"></a>
            <a href="{profile_url($mod)}" class="moderator">{$mod|display_name}</a>
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
