{include file="header.tpl"}
<div class="panel panel-danger view-description">
    <h2 class="panel-heading">{$subheading}</h2>
    <div class="panel-body">
        <p>{$message}</p>
        {$form|safe}
    </div>
</div>
{include file="footer.tpl"}
