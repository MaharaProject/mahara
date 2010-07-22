{auto_escape off}
{include file="header.tpl"}
{if !$incollection}
        <div class="message">{str tag=noviews section=collection}</div>
{else}
<table width='50%'>
    <tbody>
        {foreach from=$incollection item=view}
        <tr>
            <td>{$view->title|safe}</td>
            <td><a class="btn-del" href="{$WWWROOT}collection/deleteview.php?v={$view->view|safe}&amp;c={$view->collection|safe}">{str tag=remove}</a></td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
<fieldset>
<legend>{$addviews|safe}</legend>
{$form|safe}
</fieldset>
{include file="footer.tpl"}
{/auto_escape}
