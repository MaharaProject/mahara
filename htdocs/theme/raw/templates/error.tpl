{auto_escape off}
{include file="header.tpl" title="$title" heading="$title"}

            <div class="center">
            <h4>{$title|escape}</h4>
    		{$message|escape|nl2br}
            </div>

{include file="footer.tpl"}
{/auto_escape}
