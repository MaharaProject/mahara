{include file="header.tpl"}
<div id="staffadminlistcontainer" class="row">
    <div class="col-md-6">
        <div class="panel panel-secondary">
            <h3 class="panel-heading">{str tag=institutionadmins section=admin}</h3>
            <div class="panel-body">
                {$admins.tablerows|safe}
            </div>
        </div>
    </div>
    <div class="col-md-6">
       <div class="panel panel-secondary">
            <h3 class="panel-heading">{str tag=institutionstaff section=admin}</h3>
            <div class="panel-body">
                {$staff.tablerows|safe}
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
