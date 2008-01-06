{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
<div>
<div class="fr">
<span class="addicon">
<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id|escape}">{str tag="newtopic" section="interaction.forum}</a>
</span>
</div>
{$forum->description}
{if $admin}
<div>
<a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}">{str tag="edittitle" section="interaction.forum"}</a>
 | <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}">{str tag="deleteforum" section="interaction.forum"}</a>
</div>
{/if}
<br>
{str tag="groupownerlist" section="interaction.forum"}
<a href="{$WWWROOT}user/view.php?id={$groupowner}" class="groupowner">
<img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$groupowner}" alt="">
{$groupowner|display_name|escape}
</a>
{if $moderators}
<br>
{str tag="moderatorslist" section="interaction.forum"}
{foreach from=$moderators item=mod name=moderators}
<a href="{$WWWROOT}user/view.php?id={$mod}" class="moderator">
<img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$mod}" alt="">
{$mod|display_name|escape}</a>{if !$smarty.foreach.moderators.last}, {/if}
{/foreach}
{/if}
</div>
{$forum->subscribe}
{if $stickytopics || $regulartopics}
<form action="" method="post">
    {if !$forum->subscribed || $moderator}
    <select name="type1">
        <option value="default" selected="selected">{str tag="chooseanaction" section="interaction.forum"}</option>
        {if !$forum->subscribed}
        <option value="subscribe">{str tag="Subscribe" section="interaction.forum"}</option>
        <option value="unsubscribe">{str tag="Unsubscribe" section="interaction.forum"}</option>
        {/if}
        {if $moderator}
        <option value="sticky">{str tag="Sticky" section="interaction.forum"}</option>
        <option value="unsticky">{str tag="Unsticky" section="interaction.forum"}</option>
        <option value="closed">{str tag="Closed" section="interaction.forum"}</option>
        <option value="open">{str tag="Open" section="interaction.forum"}</option>
        {/if}
    </select>
    <input type="submit" name="updatetopics1" value="{str tag="updateselectedtopics" section="interaction.forum"}" class="submit">
    {/if}
    <table>
        <tr>
        <th></th>
        {if !$forum->subscribed || $moderator}<th></th>{/if}
        <th>{str tag="Topic" section="interaction.forum"}</th>
        <th>{str tag="Poster" section="interaction.forum"}</th>
        <th>{str tag="Posts" section="interaction.forum"}</th>
        <th>{str tag="lastpost" section="interaction.forum"}</th>
        {if $moderator}<th></th>{/if}
    </tr>
    {if $stickytopics}
    {include file="interaction:forum:topics.tpl" topics=$stickytopics moderator=$moderator forum=$forum sticky=true}
    {/if}
    {if $stickytopics && $regulartopics}<tr><td colspan="0"><hr></td></tr>{/if}
    {if $regulartopics}
    {include file="interaction:forum:topics.tpl" topics=$regulartopics moderator=$moderator forum=$forum sticky=false}
    {/if}
    </table>
    {if $regulartopics}
    <span class="center">{$pagination}</span>
    {/if}
    {if !$forum->subscribed || $moderator}
    <select name="type2">
        <option value="default" selected="selected">{str tag="chooseanaction" section="interaction.forum"}</option>
        {if !$forum->subscribed}
        <option value="subscribe">{str tag="Subscribe" section="interaction.forum"}</option>
        <option value="unsubscribe">{str tag="Unsubscribe" section="interaction.forum"}</option>
        {/if}
        {if $moderator}
        <option value="sticky">{str tag="Sticky" section="interaction.forum"}</option>
        <option value="unsticky">{str tag="Unsticky" section="interaction.forum"}</option>
        <option value="closed">{str tag="Closed" section="interaction.forum"}</option>
        <option value="open">{str tag="Open" section="interaction.forum"}</option>
        {/if}
    </select>
    <input type="submit" name="updatetopics2" value="{str tag="updateselectedtopics" section="interaction.forum"}" class="submit">
    {/if}
</form>

<h4>Key:</h4>
<ul>
    <li><img src="{$closedicon|escape}" alt="{str tag="Closed" section="interaction.forum"}"> {str tag="Closed" section="interaction.forum"}</li>
    <li><img src="{$subscribedicon|escape}" alt="{str tag="Subscribed" section="interaction.forum"}"> {str tag="Subscribed" section="interaction.forum"}</li>
    <li><span class="groupowner">{str tag="groupowner" section="interaction.forum"}</span></li>
    <li><span class="moderator">{str tag="Moderators" section="interaction.forum"}</span></li>
</ul>

{else}
{str tag="notopics" section="interaction.forum"}
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
