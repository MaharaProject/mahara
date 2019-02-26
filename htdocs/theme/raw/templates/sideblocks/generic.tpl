<div class="card card-default">
    <h3 class="card-header">
        {$sbdata.title}
    </h3>
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
