	<div class="sidebar-header panel-heading"><h3>{str tag="linksandresources"}</h3></div>
    <div class="sidebar-content panel-body">
{if $sbdata}
    <ul id="linksresources" class="list-unstyled">
{foreach from=$sbdata item=item}
      <li><a href="{$item.link}">{$item.name}</a></li>
{/foreach}
    </ul>
{/if}

</div>
