{include file="header.tpl"}
<ul class="nav nav-tabs">
    {foreach from=$navtabs item=item}
        <li class="{if $item.class}{$item.class} d-none{/if}" role="presentation" aria-hidden="true">
            <a href="{$WWWROOT}{$item.url}#profileform_{$item.page}_container" {if $item.selected}class="active"{/if}>
                {$item.title}
                <span class="accessible-hidden sr-only">({str tag=tab}{if $item.selected} {str tag=selected}{/if})</span>
            </a>
        </li>
    {/foreach}
</ul>
<div class="view-container">
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
</div>
{include file="footer.tpl"}
