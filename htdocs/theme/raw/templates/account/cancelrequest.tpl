{include file="header.tpl"}
<div id="cancelrequest" class="card bg-danger view-container">
    <h2 class="card-header">{str tag=canceldeletionrequest section=account}</h2>
    <div class="card-body">
        <p>{str tag=canceldeletionrequestconfirmation section=account arg1=$userdisplayname}</p>
        {$cancelrequestform|safe}
    </div>
</div>
{include file="footer.tpl"}
