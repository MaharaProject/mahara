{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$topic->subject}</h2>
{if $moderator}
<a href="{$WWWROOT}interaction/forum/edittopic.php?id={$topic->id|escape}">{str tag="edit"}</a>
<a href="{$WWWROOT}interaction/forum/deletetopic.php?id={$topic->id|escape}">{str tag="delete"}</a>
{/if}
{if !$topic->forumsubscribed}{$topic->subscribe}{/if}
<ul>
    <li>
        {$posts}
    </li>
</ul>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
