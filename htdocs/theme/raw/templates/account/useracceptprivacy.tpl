{include file="header.tpl"}

{if $refused}
    <div class="panel panel-danger view-container">
        <h2 class="panel-heading">{str tag="refuseprivacy" section="admin"}</h2>
        <div class="panel-body">
            <h5>{str tag="privacyrefusaldetails" section="admin"}</h5>
            <p>{str tag="confirmprivacyrefusal" section="admin"}</p>
            {$form|safe}
        </div>
    </div>
{else}
    <div class="lead">{str tag="newprivacy" section="admin"}</div>
    <div>{$form|safe}</div>
{/if}

{include file="footer.tpl"}
