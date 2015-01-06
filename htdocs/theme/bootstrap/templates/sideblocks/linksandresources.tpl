	<div class="sidebar-header"><h3>{str tag="linksandresources"}</h3></div>
    <div class="sidebar-content">
{if $sbdata}
    <ul id="linksresources">
{foreach from=$sbdata item=item}
      <li><a href="{$item.link}">{$item.name}</a></li>
{/foreach}
    </ul>
{/if}

</div>
