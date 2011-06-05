{include file="header.tpl"}
<div id="staffadminlistcontainer">
    <div class="fullwidth">
        <h3>{str tag=institutionadmins section=admin}</h3>
{$admins.tablerows|safe}
    </div>
    <div class="cb"></div>
    <div class="fullwidth">
        <h3>{str tag=institutionstaff section=admin}</h3>
{$staff.tablerows|safe}
    </div>
</div>
{include file="footer.tpl"}
