{include file="header.tpl"}
<div id="staffadminlistcontainer" class="row">
    <div class="col-md-6">
        <div class="card card-secondary">
            <h3 class="card-header">{str tag=institutionadmins section=admin}</h3>
            <div class="card-body">
                {$admins.tablerows|safe}
            </div>
        </div>
    </div>
    <div class="col-md-6">
       <div class="card card-secondary">
            <h3 class="card-header">{str tag=institutionstaff section=admin}</h3>
            <div class="card-body">
                {$staff.tablerows|safe}
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
