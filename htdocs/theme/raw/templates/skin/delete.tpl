{include file="header.tpl"}
                <div class="message delete">
                <h3>{$subheading}</h3>
                <p>{$safemessage|clean_html|safe}</p>
                {$form|safe}
                </div>
{include file="footer.tpl"}
