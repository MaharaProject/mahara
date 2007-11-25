{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<h3>{$forum->title|escape}</h3>
<p>{$forum->description}</p>
<a href="{$WWWROOT}interaction/forum/edittopic.php?forum={$forum->id|escape}">{str tag="newtopic" section="interaction.forum}</a>
<br>
{if $admin}
<a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}">{str tag="edittitle" section="interaction.forum}</a>
<br>
<a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}">{str tag="deleteforum" section="interaction.forum}</a>
<br>
{/if}
{$forum->subscribe}
<h4>{str tag="stickytopics" section=interaction.forum}</h4>
<table>
    <tr>
        <th></th>
        <th>{str tag="subject" section=interaction.forum}</th>
        <th>{str tag="poster" section=interaction.forum}</th>
        <th>{str tag="count" section=interaction.forum}</th>
        {if $moderator}
        <th>{str tag="sticky" section=interaction.forum}</th>
        <th>{str tag="closed" section=interaction.forum}</th>
        {/if}
    </tr>
    {if $stickytopics}
    {foreach from=$stickytopics item=topic}
    <tr>
        <td>X</td>
        <td><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a></td>
        <td>{$topic->poster|display_name|escape}</td>
        <td>{$topic->count|escape}</td>
        {if $moderator}
        <td>X</td>
        <td>
            {if $topic->closed}X{/if}
        </td>
        {/if}
        {if $moderator}
        <td><a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag="edit" section="interaction.forum}</a></td>
        <td><a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag="delete" section="interaction.forum}</a></td>
        {/if}
        {if !$forum->subscribed}
            <td>{$topic->subscribe}</td>
        {/if}
    </tr>
    {/foreach}
    {/if}
</table>
<h4>{str tag="regulartopics" section=interaction.forum}</h4>
<table>
    <tr>
        <th></th>
        <th>{str tag="subject" section=interaction.forum}</th>
        <th>{str tag="poster" section=interaction.forum}</th>
        <th>{str tag="count" section=interaction.forum}</th>
        {if $moderator}
        <th>{str tag="sticky" section=interaction.forum}</th>
        <th>{str tag="closed" section=interaction.forum}</th>
        {/if}
    </tr>
    {if $regulartopics}
    {foreach from=$regulartopics item=topic}
    <tr>
        <td>X</td>
        <td><a href="{$WWWROOT}interaction/forum/topic.php?id={$topic->id|escape}">{$topic->subject|escape}</a></td>
        <td>{$topic->poster|display_name|escape}</td>
        <td>{$topic->count|escape}</td>
        {if $moderator}
        <td></td>
        <td>
        {if $topic->closed}X{/if}
        </td>
        {/if}
        {if $moderator}
        <td><a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag="edit" section="interaction.forum}</a></td>
        <td><a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag="delete" section="interaction.forum}</a></td>
        {/if}
        {if !$forum->subscribed}
            <td>{$topic->subscribe}</td>
        {/if}
    </tr>
    {/foreach}
    {/if}
    
</table>

{$pagination}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
