{include file="header.tpl"}
    <div class="delete">
        <h3>{$subheading}</h3>
        <p class="lead">{$safemessage|clean_html|safe}</p>
        {$form|safe}
    </div>
{include file="footer.tpl"}
