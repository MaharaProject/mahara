{include file="header.tpl"}
<div id="staffadminlistcontainer">
    <h3>{str tag=institutionadmins section=admin}</h3>
{$admins.tablerows|safe}
    <div class="cb"></div>
    <h3>{str tag=institutionstaff section=admin}</h3>
{$staff.tablerows|safe}
</div>
{include file="footer.tpl"}
