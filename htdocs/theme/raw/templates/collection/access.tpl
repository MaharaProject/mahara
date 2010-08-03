{auto_escape on}
{include file="header.tpl"}
{if !$form}
            <div class="message">{$strnoviews|safe}</div>
{else}
            <fieldset>
            <legend>{str tag=overrideaccess section=collection}</legend>
                {$form|safe}
            </fieldset>
{/if}
{if $newform}
    {$newform|safe}
{/if}
{include file="footer.tpl"}
{auto_escape off}
