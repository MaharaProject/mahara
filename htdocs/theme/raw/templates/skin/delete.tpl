{include file="header.tpl"}
<div class="panel panel-danger mtxl">
    <h2 class="panel-heading">{$subheading}</h2>
    <div class="panel-body">
        <p>{$safemessage|clean_html|safe}</p>
        {$form|safe}
    </div>
</div>
{include file="footer.tpl"}
