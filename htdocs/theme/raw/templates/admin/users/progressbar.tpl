{include file="header.tpl"}
<p class="lead">{str tag="profilecompletenessdesc1" section="admin"}</p>
<div class="card card-body">
    <div id="progressbar-construct">
        <p>{str tag="profilecompletenesspreview" section="admin"}</p>
        {if !$enabled}
        <p>{str tag=progressbardisablednote section=admin args=$WWWROOT}</p>
        {/if}
        {$progressbarform|safe}
    </div>
</div>
{include file="footer.tpl"}
