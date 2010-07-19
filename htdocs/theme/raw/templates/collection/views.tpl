{auto_escape off}
{include file="header.tpl"}
{if !$currentviews.tablerows}
        <div class="message">{str tag=noviews section=collection}</div>
{else}
    {$currentviews.tablerows|safe}
{/if}
<fieldset>
<legend>{$addviews|safe}</legend>
{$form|safe}
</fieldset>
{include file="footer.tpl"}
{/auto_escape}
