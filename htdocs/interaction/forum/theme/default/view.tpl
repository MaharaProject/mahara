{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$groupname|escape} - {$forum->title|escape}</h3>
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
<form action="" method="post">
{if $moderator && ($stickytopics || $regulartopics)}<input type="submit" name="update" value="{str tag="update"}" class="submit">{/if}
{if $stickytopics}
<h4>{str tag="stickytopics" section="interaction.forum"}</h4>
<table>
    <tr>
        <th></th>
        <th>{str tag="topic" section="interaction.forum"}</th>
        <th>{str tag="poster" section="interaction.forum"}</th>
        <th>{str tag="posts" section="interaction.forum"}</th>
        {if $moderator}
        <th>{str tag="sticky" section="interaction.forum"}</th>
        <th>{str tag="closed" section="interaction.forum"}</th>
        <th></th>
        <th></th>
        {/if}
        <th></th>
    </tr>
    {foreach from=$stickytopics item=topic}
    <tr>
        <td>X</td>
        <td>
        <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a>
        <div>{$topic->body|escape}</div>
        </td>
        <td>
        <img src="{$WWWROOT}thumb.php?type=profileicon&amp;size=20x20&amp;id={$topic->poster}" alt="">
        {$topic->poster|display_name|escape}
        </td>
        <td>{$topic->count|escape}</td>
        {if $moderator}
        <td><input type="checkbox" name="sticky[{$topic->id|escape}]" checked="checked"></td>
        <input type=hidden name="prevsticky[{$topic->id|escape}]">
        <td>
        {if $topic->closed}
            <input type="checkbox" name="closed[{$topic->id|escape}]" checked="checked">
            <input type=hidden name="prevclosed[{$topic->id|escape}]">
        {else}
            <input type="checkbox" name="closed[{$topic->id|escape}]">
        {/if}
        </td>
        <td><a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag="edit"}</a></td>
        <td><a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag="delete"}</a></td>
        {/if}
        {if !$forum->subscribed}
            {if $topic->subscribed}
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="unsubscribe" section="interaction.forum"}" class="submit"></td>
            {else}
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="subscribe" section="interaction.forum"}" class="submit"></td>
            {/if}
        {/if}
    </tr>
    {/foreach}
</table>
{/if}
{if $regulartopics}
<h4>{str tag="regulartopics" section="interaction.forum"}</h4>
<table>
    <tr>
        <th></th>
        <th>{str tag="topic" section="interaction.forum"}</th>
        <th>{str tag="poster" section="interaction.forum"}</th>
        <th>{str tag="posts" section="interaction.forum"}</th>
        {if $moderator}
        <th>{str tag="sticky" section="interaction.forum"}</th>
        <th>{str tag="closed" section="interaction.forum"}</th>
        <th></th>
        <th></th>
        {/if}
        <th></th>
    </tr>
    {foreach from=$regulartopics item=topic}
    <tr>
        <td>X</td>
        <td>
        <a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a>
        <div>{$topic->body|escape}</div>
        </td>
        <td>
        <img src="{$WWWROOT}thumb.php?type=profileicon&amp;size=20x20&amp;id={$topic->poster}" alt="">
        {$topic->poster|display_name|escape}
        </td>
        <td>{$topic->count|escape}</td>
        {if $moderator}
        <td><input type="checkbox" name="sticky[{$topic->id|escape}]"></td>
        <td>
        {if $topic->closed}
            <input type="checkbox" name="closed[{$topic->id|escape}]" checked="checked">
            <input type=hidden name="prevclosed[{$topic->id|escape}]">
        {else}
            <input type="checkbox" name="closed[{$topic->id|escape}]">
        {/if}
        </td>
        <td><a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag="edit"}</a></td>
        <td><a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag="delete"}</a></td>
        {/if}
        {if !$forum->subscribed}
            {if $topic->subscribed}
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="unsubscribe" section="interaction.forum"}" class="submit"></td>
            {else}
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="subscribe" section="interaction.forum"}" class="submit"></td>
            {/if}
        {/if}
    </tr>
    {/foreach}
</table>
<span class="center">{$pagination}</span>
{else}
<h4>{str tag="noregulartopics" section="interaction.forum"}</h4>
{/if}
</form>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
