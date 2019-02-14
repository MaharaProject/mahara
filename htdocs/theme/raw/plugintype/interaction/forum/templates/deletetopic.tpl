{include file="header.tpl"}

<div class="card bg-danger view-container">
    <h2 class="card-header">{$subheading}</h2>
    <div class="card-body">
        {$deleteform|safe}
    </div>
</div>

{include file="interaction:forum:simplepost.tpl" post=$topic groupadmins=$groupadmins}

{include file="footer.tpl"}
