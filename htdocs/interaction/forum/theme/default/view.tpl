{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<h2>{$groupname|escape}</h2>
<h3>{$forum->title|escape}</h3>
<p>{$forum->description}</p>
<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id|escape}">{str tag="newtopic" section="interaction.forum}</a>
{if $admin}
<a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}">{str tag="edit"}</a>
<a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}">{str tag="delete"}</a>
{/if}
{$forum->subscribe}
<form action="" method="post">
{if $moderator}<input type="submit" name="update" value="{str tag="update"}">{/if}
{if $stickytopics}
<h4>{str tag="stickytopics" section="interaction.forum"}</h4>
<table>
    <tr>
        <th></th>
        <th>{str tag="subject" section="interaction.forum"}</th>
        <th>{str tag="poster" section="interaction.forum"}</th>
        <th>{str tag="count" section="interaction.forum"}</th>
        {if $moderator}
        <th>{str tag="sticky" section="interaction.forum"}</th>
        <th>{str tag="closed" section="interaction.forum"}</th>
        {/if}
    </tr>
    {foreach from=$stickytopics item=topic}
    <tr>
        <td>X</td>
        <td><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a></td>
        <td>{$topic->poster|display_name|escape}</td>
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
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="unsubscribe" section="interaction.forum"}"></td>
            {else}
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="subscribe" section="interaction.forum"}"></td>
            {/if}
        {/if}
    </tr>
    {/foreach}
</table>
{else}
<h4>{str tag="nostickytopics" section="interaction.forum"}</h4>
{/if}
{if $regulartopics}
<h4>{str tag="regulartopics" section="interaction.forum"}</h4>
<table>
    <tr>
        <th></th>
        <th>{str tag="subject" section="interaction.forum"}</th>
        <th>{str tag="poster" section="interaction.forum"}</th>
        <th>{str tag="count" section="interaction.forum"}</th>
        {if $moderator}
        <th>{str tag="sticky" section="interaction.forum"}</th>
        <th>{str tag="closed" section="interaction.forum"}</th>
        {/if}
    </tr>
    {foreach from=$regulartopics item=topic}
    <tr>
        <td>X</td>
        <td><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a></td>
        <td>{$topic->poster|display_name|escape}</td>
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
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="unsubscribe" section="interaction.forum"}"></td>
            {else}
                <td><input type="submit" name="subscribe[{$topic->id}]" value="{str tag="subscribe" section="interaction.forum"}"></td>
            {/if}
        {/if}
    </tr>
    {/foreach}
</table>
{else}
<h4>{str tag="noregulartopics" section="interaction.forum"}</h4>
{/if}
{$pagination}
</form>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
