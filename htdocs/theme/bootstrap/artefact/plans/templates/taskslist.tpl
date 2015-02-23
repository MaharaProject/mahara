{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <tr class="incomplete">
            <td class="completiondate">{$task->completiondate}</td>
            <td class="plantasktitle">{$task->title}</td>
            <td class="plantaskdescription">{$task->description|clean_html|safe}</td>
            <td class="incomplete">
                <span class="fa fa-exclamation-triangle"></span>
                <span class="sr-only">{str tag=overdue section=artefact.plans}</span>
            </td>
    {else}
        <tr class="{cycle values='r0,r1'}">
            <td class="completiondate">{$task->completiondate}</td>
            <td class="plantasktitle">{$task->title}</td>
            <td class="plantaskdescription">{$task->description|clean_html|safe}</td>
            {if $task->completed == 1}
                <td class="completed">
                    <span class="fa fa-check"></span>
                    <span class="sr-only">{str tag=completed section=artefact.plans}</span>
                </td>
            {else}
                <td><span class="accessible-hidden sr-only">{str tag=incomplete section=artefact.plans}</span></td>
            {/if}

    {/if}
            <td class="planscontrols">
                <a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}" title="{str tag=edit}" class="btn btn-default btn-xs">
                    <span class="fa fa-pencil"></span>
                    <span class="sr-only">{str(tag=editspecific arg1=$task->title)|escape:html|safe}</span>
                </a>
                <a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}" title="{str tag=delete}" class="btn btn-danger btn-xs">
                    <span class="fa fa-trash"></span>
                    <span class="sr-only">{str(tag=deletespecific arg1=$task->title)|escape:html|safe}</span>
                </a>
            </td>
        </tr>
{/foreach}
