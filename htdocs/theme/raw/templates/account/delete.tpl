{include file="header.tpl"}
<div id="deleteaccount" class="panel panel-danger view-container">
    <h2 class="panel-heading">{str tag=deleteaccount1}</h2>
    <div class="panel-body">
        {if $requiresapproval}
            <p>{str tag=sendnotificationdescription section=account}</p>
        {else}
            <p>{str tag=deleteaccountdescription section=account}</p>
        {/if}
        {$delete_form|safe}
    </div>
</div>
{include file="footer.tpl"}
