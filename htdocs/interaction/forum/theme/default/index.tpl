{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<h2>{$heading|escape}</h2>
<div>
{include file="interaction:forum:breadcrumbs.tpl" breadcrumbs=$breadcrumbs}
{if $admin}
<div class="fr">
<span class="addicon">
<a href="{$WWWROOT}interaction/edit.php?group={$groupid|escape}&amp;plugin=forum">{str tag="newforum" section=interaction.forum}</a>
</span>
</div>
{/if}
<br>
{str tag="groupownerlist" section="interaction.forum"}
<a href="{$WWWROOT}user/view.php?id={$groupowner}" class="groupowner">
<img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$groupowner}" alt="">
{$groupowner|display_name|escape}</a>
</div>
{if $forums}
<ul>
    {foreach from=$forums item=forum}
    <li>
        <h4><a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id|escape}">{$forum->title|escape}</a></h4>
        {$forum->description}
        {if $forum->moderators}
        <br>
        {str tag="moderatorslist" section="interaction.forum"}
        {foreach from=$forum->moderators item=mod name=moderators}
            <a href="{$WWWROOT}user/view.php?id={$mod}" class="moderator">
            <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=20&amp;id={$mod}" alt="">
            {$mod|display_name|escape}</a>{if !$smarty.foreach.moderators.last}, {/if}
        {/foreach}
        {/if}
        <div class="fr">{str tag=topics section=interaction.forum args=$forum->count}</div>
        {if $admin}
        <div>
        <a href="{$WWWROOT}interaction/edit.php?id={$forum->id|escape}&amp;returnto=index">{str tag=edit}</a>
         | <a href="{$WWWROOT}interaction/delete.php?id={$forum->id|escape}&amp;returnto=index">{str tag=delete}</a>
        </div>
        {/if}
        {$forum->subscribe}
    </li>
    {/foreach}
</ul>
{else}
<div class="message">{str tag=noforums section=interaction.forum}</div>
{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
