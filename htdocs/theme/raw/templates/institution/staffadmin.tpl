{include file="header.tpl"}
<div id="staffadminlistcontainer" class="row">
    <div class="col-md-{$size}">
       <div class="card card-secondary">
            <h2 class="card-header">{str tag=institutionstaff section=admin}</h2>
            <div class="block">
                {$staff.tablerows|safe}
            </div>
        </div>
    </div>
    {if !$issite}
    <div class="col-md-{$size}">
       <div class="card card-secondary">
            <h2 class="card-header">{str tag=institutionsupportadmin section=admin}</h2>
            <div class="block">
                {$supportadmin.tablerows|safe}
            </div>
        </div>
    </div>
    {/if}
    <div class="col-md-{$size}">
        <div class="card card-secondary">
            <h2 class="card-header">{str tag=institutionadmins section=admin}</h2>
            <div class="block">
                {$admins.tablerows|safe}
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
