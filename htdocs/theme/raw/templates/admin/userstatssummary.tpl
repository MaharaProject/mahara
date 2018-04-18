<div>
    <h4>{str tag=youraverageuser section=admin}</h4>
    <ul class="list-group list-group-lite unstyled">
        <li class="list-group-item">{$data.strmaxfriends|safe}</li>
        <li class="list-group-item">{$data.strmaxviews|safe}</li>
        <li class="list-group-item">{$data.strmaxgroups|safe}</li>
        <li class="list-group-item">{$data.strmaxquotaused|safe}</li>
    </ul>
</div>
{if $data}
    <h4>{str tag="usersbyinstitution" section="statistics"}</h4>
    <div class="card-body">
        <canvas class="graphcanvas" id="sitestatsusersgraph"></canvas>
        <script>
        {literal}
        jQuery(function() {
            fetch_graph_data({
                'id':'sitestatsusersgraph',
                'type':'pie',
                'graph':'user_institution_graph',
                'extradata': {
                    'configs': {
                        'multiTooltipTemplate': "<%if (datasetLabel){%><%=datasetLabel%>: <%}%><%= value %>"
                    }
                }
             });
        });
        {/literal}
        </script>
    </div>
{/if}
