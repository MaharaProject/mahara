{include file="header.tpl"}
<div id="deleteaccount">
    <div class="panel panel-danger mtxl">
        <h2 class="panel-heading">{str tag=deleteaccount section=account}</h2>
        <div class="panel-body">
            <p>{str tag=deleteaccountdescription section=account}</p>
            {$delete_form|safe}
        </div>
    </div>
</div>
{include file="footer.tpl"}