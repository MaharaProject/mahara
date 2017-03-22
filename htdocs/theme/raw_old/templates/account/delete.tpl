{include file="header.tpl"}
<div id="deleteaccount" class="panel panel-danger view-container">
    <h2 class="panel-heading">{str tag=deleteaccount section=account}</h2>
    <div class="panel-body">
        <p>{str tag=deleteaccountdescription section=account}</p>
        {$delete_form|safe}
    </div>
</div>
{include file="footer.tpl"}