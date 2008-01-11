    <h3>{str tag="mainmenu"}</h3>

{if $data}
    <ul id="sitemenu">
{foreach from=$data item=item}
    	<li class="{cycle values=r0,r1}"><a href="{$item.link|escape}">{$item.name}</a></li>
{/foreach}
    </ul>
{/if}

