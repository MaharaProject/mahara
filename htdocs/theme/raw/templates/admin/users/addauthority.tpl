{include file="header.tpl"}
<div class="card card-secondary">
    <h3 class="card-heading">{str tag="adminauthorities" section="admin"}</h3>
    <div class="card-body">
    {$auth_imap_form|safe}
    </div>
</div>
{include file="footer.tpl"}
