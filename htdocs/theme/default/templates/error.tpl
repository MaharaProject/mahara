{include file="header.tpl" title="$title" heading="$title"}

{include file="columnfullstart.tpl"}
            <div class="center">
            <h4>{$title|escape}</h4>
    		{$message|escape|nl2br}
            </div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
