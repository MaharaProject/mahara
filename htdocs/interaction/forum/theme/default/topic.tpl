{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<p>
<a href="{$breadcrumbs[0][0]|escape}">{$breadcrumbs[0][1]|escape}</a>
{foreach from=$breadcrumbs[1] item=item}
&raquo <a href="{$item[0]|escape}">{$item[1]|escape}</a>
{/foreach}
</p>

<h2>{$topic->forumtitle|escape}</h2>
<h3>{$topic->subject|escape}</h3>
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
