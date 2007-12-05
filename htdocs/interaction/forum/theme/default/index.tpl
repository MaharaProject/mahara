{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<p>
<a href="{$breadcrumbs[0][0]|escape}">{$breadcrumbs[0][1]|escape}</a>
{foreach from=$breadcrumbs[1] item=item}
&raquo <a href="{$item[0]|escape}">{$item[1]|escape}</a>
{/foreach}
</p>

<h2>{$groupname|escape}</h2>
<h3>{str tag=nameplural section=interaction.forum}</h3>
{if $admin}
<a href="{$WWWROOT}interaction/edit.php?group={$groupid|escape}&plugin=forum">{str tag="newforum" section=interaction.forum}</a>
{/if}
{if $forums}
<ul>
    {foreach from=$forums item=forum}
    <li>
        <a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id|escape}">{$forum->title|escape}</a>
        <p>{$forum->description}</p>
        <div class="fr">{$forum->count|escape}</div>
        {if $admin}
        <a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}">{str tag=edit}</a>
        <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}">{str tag=delete}</a>
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
