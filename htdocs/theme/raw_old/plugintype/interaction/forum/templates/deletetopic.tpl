{include file="header.tpl"}

<div class="panel panel-danger view-container">
    <h2 class="panel-heading">{$subheading}</h2>
    <div class="panel-body">
        {$deleteform|safe}
    </div>
</div>

{include file="interaction:forum:simplepost.tpl" post=$topic groupadmins=$groupadmins}

{include file="footer.tpl"}
