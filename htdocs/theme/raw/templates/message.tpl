{include file="header.tpl"}
    <div class="message alert alert-{$type}">
    {$message|clean_html|safe}
    </div>
{include file="footer.tpl"}
