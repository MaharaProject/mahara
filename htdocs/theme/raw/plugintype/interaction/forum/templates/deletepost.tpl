{include file="header.tpl"}

<div class="card card bg-danger text-white view-container">
    <h2 class="card-heading">{$subheading}</h2>
    <div class="card-body">
        {$deleteform|safe}
    </div>
</div>

{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins}

{include file="footer.tpl"}
