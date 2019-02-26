<h4>{str tag=youraverageuser section=admin}</h4>
<ul class="list-group list-group-lite unstyled">
    <li class="list-group-item">{$data.strmaxfriends|safe}</li>
    <li class="list-group-item">{$data.strmaxviews|safe}</li>
    <li class="list-group-item">{$data.strmaxgroups|safe}</li>
    <li class="list-group-item">{$data.strmaxquotaused|safe}</li>
</ul>
{if $data}
    <h4>{str tag=usersbytype section=statistics}</h4>
    <div class="card-body">
        <canvas class="graphcanvas" id="sitestatsusersgraph" width="300" height="200"></canvas>
        <script>
        {literal}
        jQuery(function() {
            fetch_graph_data({
                'id':'sitestatsusersgraph',
                'type':'pie',
                'graph':'institution_user_type_graph',
                'extradata': {
                    'institution': '{/literal}{$data.institution}{literal}'
                }
            });
        });
        {/literal}
        </script>
    </div>
{/if}
