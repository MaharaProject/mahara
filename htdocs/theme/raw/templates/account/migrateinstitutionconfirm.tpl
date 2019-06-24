{include file="header.tpl"}
<p class="lead">{str tag="migrateaccountconfirm" section="mahara" arg1="$sitename"}</p>
{if $error}
    <div class="alert alert-danger">{$error}</div>
    <div>{$errorlink|safe}</div>
{else}
    {if $confirmform}
    <p>{$confirmforminfo}</p>
    <div class="view-container">
        {$confirmform|safe}
    </div>
    {else}
    {$success}
    {/if}
{/if}

{include file="footer.tpl"}

