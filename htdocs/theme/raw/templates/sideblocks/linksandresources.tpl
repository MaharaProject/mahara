{if $sbdata}
<div class="card">
    <h2 class="card-header">
        {str tag="linksandresources"}
    </h2>
    <ul class="list-group">
    {foreach from=$sbdata item=item}
        <li class="list-group-item">
            <a href="{$item.link}">{$item.name}</a>
        </li>
    {/foreach}
    </ul>
</div>
{/if}
