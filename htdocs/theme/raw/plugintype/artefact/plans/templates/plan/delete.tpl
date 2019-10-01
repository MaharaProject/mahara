{include file="header.tpl"}
<div class="card bg-danger view-description">
    <h2 class="card-header">{$subheading}</h2>
    <div class="card-body">
        <p>{$message}</p>
        {$form|safe}
    </div>
</div>
{include file="footer.tpl"}
