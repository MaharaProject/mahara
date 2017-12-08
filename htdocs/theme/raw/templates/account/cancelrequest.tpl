{include file="header.tpl"}
<div id="cancelrequest" class="panel panel-danger view-container">
    <h2 class="panel-heading">{str tag=canceldeletionrequest section=account}</h2>
    <div class="panel-body">
        <p>{str tag=canceldeletionrequestconfirmation section=account arg1=$userdisplayname}</p>
        {$cancelrequestform|safe}
    </div>
</div>
{include file="footer.tpl"}
