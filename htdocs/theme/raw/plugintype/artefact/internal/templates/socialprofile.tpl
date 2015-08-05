{include file="header.tpl"}
<ul class="nav nav-tabs">
    {foreach from=$navtabs item=item}
        <li class="{if $item.class}{$item.class} {/if}{if $item.selected} active{/if}" role="presentation">
            <a href="{$WWWROOT}{$item.url}#profileform_{$item.page}_container">
                {$item.title}
                <span class="accessible-hidden sr-only">({str tag=tab}{if $item.selected} {str tag=selected}{/if})</span>
            </a>
        </li>
    {/foreach}
</ul>
<div class="pbxl"></div>
{if $message}
    <div class="deletemessage">
        <h2>{$subheading}</h2>
        <p class="lead text-small">{$message}</p>
        <div>{$form|safe}</div>
    </div>
{else}
    <h2>{$subheading}</h2>
    <div>{$form|safe}</div>
{/if}
{include file="footer.tpl"}