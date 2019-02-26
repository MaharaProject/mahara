{include file="header.tpl"}
<div class="card">
    <h3 class="card-header">{str tag="adminauthorities" section="admin"}</h3>
    <div class="card-body">
    {$auth_imap_form|safe}
    </div>
</div>
{include file="footer.tpl"}
