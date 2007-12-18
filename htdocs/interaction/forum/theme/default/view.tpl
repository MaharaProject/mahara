{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$groupname|escape} - {$forum->title|escape}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
<div>
{$forum->description}
</div>
<div class="fr"><span class="addicon">
<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id|escape}">{str tag="newtopic" section="interaction.forum}</a>
</span></div>
{if $admin}
<div>
<a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}">{str tag="edittitle" section="interaction.forum"}</a>
 | <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}">{str tag="deleteforum" section="interaction.forum"}</a>
</div>
{/if}
{$forum->subscribe}
{if $stickytopics || $regulartopics}
<form action="" method="post">
    {if !$forum->subscribed || $moderator}
    <select name="type1">
        {if !$forum->subscribed}
        <option value="subscribe">{str tag="subscribe" section="interaction.forum"}</option>
        <option value="unsubscribe">{str tag="unsubscribe" section="interaction.forum"}</option>
        {/if}
        {if $moderator}
        <option value="sticky">{str tag="sticky" section="interaction.forum"}</option>
        <option value="unsticky">{str tag="unsticky" section="interaction.forum"}</option>
        <option value="closed">{str tag="closed" section="interaction.forum"}</option>
        <option value="open">{str tag="open" section="interaction.forum"}</option>
        {/if}
    </select>
    <input type="submit" name="updatetopics1" value="{str tag="updatetopics" section="interaction.forum"}" class="submit">
    {/if}
    <table>
        <tr>
        <th> &nbsp; </th>
        {if !$forum->subscribed || $moderator}<th></th>{/if}
        <th>{str tag="topic" section="interaction.forum"}</th>
        <th>{str tag="poster" section="interaction.forum"}</th>
        <th>{str tag="posts" section="interaction.forum"}</th>
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
        {if !$forum->subscribed}
        <option value="subscribe">{str tag="subscribe" section="interaction.forum"}</option>
        <option value="unsubscribe">{str tag="unsubscribe" section="interaction.forum"}</option>
        {/if}
        {if $moderator}
        <option value="sticky">{str tag="sticky" section="interaction.forum"}</option>
        <option value="unsticky">{str tag="unsticky" section="interaction.forum"}</option>
        <option value="closed">{str tag="closed" section="interaction.forum"}</option>
        <option value="open">{str tag="open" section="interaction.forum"}</option>
        {/if}
    </select>
    <input type="submit" name="updatetopics2" value="{str tag="updatetopics" section="interaction.forum"}" class="submit">
    {/if}
</form>
{else}
{str tag="notopics" section="interaction.forum"}
{/if}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
