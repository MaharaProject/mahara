{include file="header.tpl"}
    <p class="lead">{str tag=sitelicensesdescription section=admin}</p>
    <div class="panel panel-default">
        <div class="panel-body">
        {if !$enabled}
        <p class="alert alert-warning">{str tag=sitelicensesdisablednote section=admin args=$WWWROOT}</p>
        {/if}
        {$form|safe}
        </div>
    </div>
{include file="footer.tpl"}
