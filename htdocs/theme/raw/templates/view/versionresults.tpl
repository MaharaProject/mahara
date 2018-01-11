{foreach from=$versions item=view}
    <tr>
        <td>
            {$view->id}
        </td>
        <td>
            {$view->viewname}
        </td>
        <td>
            {$view->owner}
        </td>
        <td>
            {$view->institution}
        </td>
    </tr>
{/foreach}
{$pagination|safe}
