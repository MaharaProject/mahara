{include file="header.tpl"}
<div id="deleteaccount" class="card bg-danger view-container">
    <h2 class="card-header">{str tag=deleteaccount1}</h2>
    <div class="card-body">
        {if $requiresapproval}
            <p>{str tag=sendnotificationdescription section=account}</p>
        {else}
            <p>{str tag=deleteaccountdescription section=account}</p>
        {/if}
        {$delete_form|safe}
    </div>
</div>
{include file="footer.tpl"}
