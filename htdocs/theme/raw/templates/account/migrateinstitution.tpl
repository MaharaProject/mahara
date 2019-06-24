{include file="header.tpl"}
{str tag="migrateinstitutiondescription" section="mahara" arg1=$sitename}

{if $postformresponse}
    <div class="alert alert-success">{$postformresponse|safe}</div>
{elseif $migrateform}
<div class="view-container">
    <h2>{str tag="migrateaccounttoinstitution"}</h2>
    {$migrateform|safe}
</div>
{else}
<div class="view-container">
    <p>{str tag=nomigrateoptions section=mahara}</p>
</div>
{/if}

{if $saml_logout}
<script>
window.top.location.href = '{$WWWROOT}account/migrateinstitution.php';
</script>
{/if}

{include file="footer.tpl"}

