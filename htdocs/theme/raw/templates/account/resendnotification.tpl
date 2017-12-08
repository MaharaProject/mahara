{include file="header.tpl"}
<div id="resendnotification" class="panel panel-danger view-container">
    <h2 class="panel-heading">{str tag=resenddeletionnotification section=account}</h2>
    <div class="panel-body">
        <p>{str tag=sendnotificationdescription section=account}</p>
        {$delete_form|safe}
    </div>
</div>
{include file="footer.tpl"}
