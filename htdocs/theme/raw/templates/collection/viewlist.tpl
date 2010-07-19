{auto_escape off}
<table width='50%'>
    <tbody>
        {foreach from=$currentviews item=view}
        <tr>
            <td>{$view->title|escape}</td>
            <td><a class="btn-del" href="{$WWWROOT}collection/deleteview.php?id={$view->view|escape}">{str tag=remove}</a></td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/auto_escape}
