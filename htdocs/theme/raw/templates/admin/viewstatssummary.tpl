{if $viewcount == 0}
<p class="lead small-text">{str tag=noviews1 section=view}</p>
{/if}
{if $blocktypecounts}
    <h4>{str tag=blockcountsbytype section=admin}</h4>
    <ul class="list-group list-group-lite unstyled">
    {foreach from=$blocktypecounts item=item}
        <li class="list-group-item">{$item->title}: {$item->blocks}</li>
    {/foreach}
    </ul>
{/if}
{if $viewtypes}
    <div class="card-body">
        <h4>{str tag=viewsbytype section=admin}</h4>
        <canvas class="graphcanvas" id="sitestatsviewtypesgraph"></canvas>
        <script>
        {literal}
        jQuery(function() {
            fetch_graph_data({'id':'sitestatsviewtypesgraph','type':'doughnut','graph':'view_type_graph_render'});
        });
        {/literal}
        </script>
    </div>
{/if}
