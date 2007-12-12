{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$groupname|escape} - {str tag=nameplural section=interaction.forum}</h2>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
{if $admin}
<div class="fr">
<span class="addicon">
<a href="{$WWWROOT}interaction/edit.php?group={$groupid|escape}&amp;plugin=forum">{str tag="newforum" section=interaction.forum}</a>
</span>
</div>
{/if}
{if $forums}
<ul>
    {foreach from=$forums item=forum}
    <li>
        <h4><a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id|escape}">{$forum->title|escape}</a></h4>
        {$forum->description}
        <div class="fr">{str tag=topics section=interaction.forum args=$forum->count}</div>
        {if $admin}
        <div>
        <a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}">{str tag=edit}</a>
         | <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}">{str tag=delete}</a>
        </div>
        {/if}
        {$forum->subscribe}
    </li>
    {/foreach}
</ul>
{else}
<p>{str tag=noforums section=interaction.forum}</p>
{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
