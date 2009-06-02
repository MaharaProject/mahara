<h3>{str tag="linksandresources"}</h3>

    <div class="sidebar-content">
{if $data}
    <ul>
{foreach from=$data item=item}
    	<li><strong><a href="{$item.link|escape}">{$item.name}</a></strong></li>
{/foreach}
    </ul>
{/if}

</div>
