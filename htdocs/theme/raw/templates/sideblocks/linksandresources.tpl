{if $sbdata}
<div class="card">
    <h3 class="card-header">
        {str tag="linksandresources"}
    </h3>
    <ul class="list-group">
    {foreach from=$sbdata item=item}
        <li class="list-group-item">
            <a href="{$item.link}">{$item.name}</a>
        </li>
    {/foreach}
    </ul>
</div>
{/if}
