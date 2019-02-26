{include file="header.tpl"}
    <p class="lead">{str tag=sitelicensesdesc section=admin}</p>
    <div class="card">
        <div class="card-body">
        {if !$enabled}
        <p class="alert alert-warning">{str tag=sitelicensesdisablednote section=admin args=$WWWROOT}</p>
        {/if}
        {$form|safe}
        </div>
    </div>
{include file="footer.tpl"}
