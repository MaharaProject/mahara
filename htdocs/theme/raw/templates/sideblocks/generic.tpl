<div class="card card-default">
    <h2 class="card-header">
        {$sbdata.title}
    </h2>
    {if $sbdata.data}
    <ul class="list-group">
        {foreach from=$sbdata.data item=item}
            <li class="list-group-item list-unstyled">
                {$item}
            </li>
        {/foreach}
    </ul>
    {else}
        {$sbdata.content}
    {/if}
</div>
