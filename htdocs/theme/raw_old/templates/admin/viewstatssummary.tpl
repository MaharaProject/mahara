{if $viewcount == 0}
<p class="lead small-text">{str tag=noviews1 section=view}</p>
{/if}
{if $blocktypecounts}
    <h4>{str tag=blockcountsbytype section=admin}</h4>
    <ul class="list-group list-group-lite unstyled">
    {foreach from=$blocktypecounts item=item}
        <li class="list-group-item">{str tag=title section=blocktype.$item->langsection}: {$item->blocks}</li>
    {/foreach}
    </ul>
    {if $viewtypes}
    <h4>{str tag=viewsbytype section=admin}</h4>
    <div class="panel-body">
        <canvas class="graphcanvas" id="sitestatsviewtypesgraph"></canvas>
        <script type="application/javascript">
        {literal}
        jQuery(function() {
            fetch_graph_data({'id':'sitestatsviewtypesgraph','type':'doughnut','graph':'view_type_graph_render'});
        });
        {/literal}
        </script>
    </div>
    {/if}
{/if}
