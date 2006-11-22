<div class="sidebar">
{str tag="mainmenu"}
    <ul id="sitemenu">
{foreach from=$site_menu item=item}
    <li><a href="{$item.link|escape}">{$item.name}</a></li>
{/foreach}
    </ul>
</div>
