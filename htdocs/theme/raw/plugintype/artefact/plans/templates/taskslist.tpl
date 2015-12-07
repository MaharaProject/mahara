{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <tr class="incomplete danger">
            <td class="incomplete">
                <span class="icon icon-times icon-lg left text-danger" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=overdue section=artefact.plans}</span>
            </td>
            <td class="plantasktitle">{$task->title}</td>
            <td class="completiondate"><strong>{$task->completiondate}</strong></td>

            <td class="plantaskdescription">
            {$task->description|clean_html|safe}
            {if $task->tags}<span>{str tag=tags}: </span>{list_tags owner=$task->owner tags=$task->tags}{/if}
            </td>

    {else}
        <tr class="complete">
            {if $task->completed == 1}
                <td class="completed text-center">
                    <span class="icon icon-check-square-o icon-lg left text-success" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=completed section=artefact.plans}</span>
                </td>
            {else}
                <td class="text-center">
                    <span class="accessible-hidden sr-only">{str tag=incomplete section=artefact.plans}</span>
                </td>
            {/if}
            <td class="plantasktitle">{$task->title}</td>
            <td class="completiondate">{$task->completiondate}</td>

            <td class="plantaskdescription">
                {$task->description|clean_html|safe}
                {if $task->tags}<span>{str tag=tags}: </span>{list_tags owner=$task->owner tags=$task->tags}{/if}
            </td>


    {/if}
            <td class="planscontrols control-buttons text-right">
                <div class="btn-group">
                    <a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}" title="{str tag=edit}" class="btn btn-default btn-xs">
                        <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str(tag=editspecific arg1=$task->title)|escape:html|safe}</span>
                    </a>
                    <a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}" title="{str tag=delete}" class="btn btn-default btn-xs">
                        <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str(tag=deletespecific arg1=$task->title)|escape:html|safe}</span>
                    </a>
                </div>
            </td>
        </tr>
{/foreach}
