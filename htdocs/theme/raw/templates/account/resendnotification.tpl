{include file="header.tpl"}
<div id="resendnotification" class="card bg-danger view-container">
    <h2 class="card-header">{str tag=resenddeletionnotification section=account}</h2>
    <div class="card-body">
        <p>{str tag=sendnotificationdescription section=account}</p>
        {$delete_form|safe}
    </div>
</div>
{include file="footer.tpl"}
