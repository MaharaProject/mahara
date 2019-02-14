{include file="header.tpl"}
<div class="card bg-danger view-container">
    <h2 class="card-header">{$subheading}</h2>
    <div class="card-body">
        <p>{$safemessage|clean_html|safe}</p>
        {$form|safe}
    </div>
</div>
{include file="footer.tpl"}
