{include file="header.tpl"}
<div id="cancelrequest" class="card card bg-danger text-white view-container">
    <h2 class="card-heading">{str tag=canceldeletionrequest section=account}</h2>
    <div class="card-body">
        <p>{str tag=canceldeletionrequestconfirmation section=account arg1=$userdisplayname}</p>
        {$cancelrequestform|safe}
    </div>
</div>
{include file="footer.tpl"}
