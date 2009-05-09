{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<div class="forumaddicon fr">
	{if $admin}
		<a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}" id="btn-editforum">{str tag="edittitle" section="interaction.forum"}</a>
		<a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}" id="btn-deleteforum">{str tag="deleteforum" section="interaction.forum"}</a>
	{/if}
	{$forum->subscribe}
</div>
<h2>{$subheading|escape}</h2>
<div id="forumdescription">{$forum->description}</div>
<div id="viewforum">
	<h3>{str tag=Topics section="interaction.forum"}</h3>
	<span class="forumaddicon fr">
	<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id|escape}" id="btn-newtopic">{str tag="newtopic" section="interaction.forum}</a>
	</span>
	
	<label>{str tag="groupadminlist" section="interaction.forum"}</label>
	{foreach name=groupadmins from=$groupadmins item=groupadmin}<a href="{$WWWROOT}user/view.php?id={$groupadmin}" class="groupadmin s">
	<img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$groupadmin}" alt="">
	{$groupadmin|display_name|escape}</a>{if !$smarty.foreach.groupadmins.last}, {/if}{/foreach}
	{if $moderators}
	<br>
	<label>{str tag="moderatorslist" section="interaction.forum"}</label>
	{foreach from=$moderators item=mod name=moderators}
	<a href="{$WWWROOT}user/view.php?id={$mod}" class="moderator">
	<img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$mod}" alt="">
	{$mod|display_name|escape}</a>{if !$smarty.foreach.moderators.last}, {/if}
	{/foreach}
	{/if}
</div>
{if $stickytopics || $regulartopics}
<form action="" method="post">
    {if $membership && (!$forum->subscribed || $moderator)}
    <div class="forumselectwrap"><select name="type1">
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
    <input type="submit" name="updatetopics1" value="{str tag="updateselectedtopics" section="interaction.forum"}" class="submit">
		{if $moderator}
			{contextualhelp plugintype='interaction' pluginname='forum' section='updatemod'}
		{else}
			{contextualhelp plugintype='interaction' pluginname='forum' section='update'}
		{/if}
	</div>	
    {/if}
    <table id="forumtopicstable" class="fullwidth">
        <tr>
        <th></th>
        {if $membership && (!$forum->subscribed || $moderator)}<th></th>{/if}
        <th>{str tag="Topic" section="interaction.forum"}</th>
        <th>{str tag="Poster" section="interaction.forum"}</th>
        <th class="postscount">{str tag="Posts" section="interaction.forum"}</th>
        <th>{str tag="lastpost" section="interaction.forum"}</th>
        {if $moderator}<th></th>{/if}
    </tr>
    {if $stickytopics}
    	{include file="interaction:forum:topics.tpl" topics=$stickytopics moderator=$moderator forum=$forum sticky=true}
    {/if}
    {if $stickytopics && $regulartopics}<tr><td></td></tr>{/if}
    {if $regulartopics}
    	{include file="interaction:forum:topics.tpl" topics=$regulartopics moderator=$moderator forum=$forum sticky=false}
    {/if}
    </table>
    {if $regulartopics}
    	<div class="fr">{$pagination}</div>
    {/if}
    {if $membership && (!$forum->subscribed || $moderator)}
    <div class="forumselectwrap fl"><select name="type2">
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
    <input type="submit" name="updatetopics2" value="{str tag="updateselectedtopics" section="interaction.forum"}" class="submit">
		{if $moderator}
			{contextualhelp plugintype='interaction' pluginname='forum' section='updatemod'}
		{else}
			{contextualhelp plugintype='interaction' pluginname='forum' section='update'}
		{/if}
	</div>	
    {/if}
</form>

<div class="forumkey">
<label>{str tag="Key" section="interaction.forum"}:</label>
<ul id="forumkeylist">
    <li><img src="{$closedicon|escape}" alt="{str tag="Closed" section="interaction.forum"}"> {str tag="Closed" section="interaction.forum"}</li>
    <li><img src="{$subscribedicon|escape}" alt="{str tag="Subscribed" section="interaction.forum"}"> {str tag="Subscribed" section="interaction.forum"}</li>
    <li><span class="groupadmin">{str tag="groupadmins" section="interaction.forum"}</span></li>
    <li><span class="moderator">{str tag="Moderators" section="interaction.forum"}</span></li>
</ul>
</div>
{else}
<p>{str tag="notopics" section="interaction.forum"}</p>
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
