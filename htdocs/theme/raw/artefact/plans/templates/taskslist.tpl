{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <tr class="incomplete">
            <td class="completiondate">{$task->completiondate}</td>
            <td class="plantasktitle">{$task->title}</td>
            <td class="plantaskdescription">{$task->description|clean_html|safe}</td>
            <td class="plantasktags">{if $task->tags}{list_tags owner=$task->owner tags=$task->tags}{/if}</td>
            <td class="incomplete"><img src="{$WWWROOT}theme/raw/static/images/failure_small.png" alt="{str tag=overdue section=artefact.plans}" /></td>
    {else}
        <tr class="{cycle values='r0,r1'}">
            <td class="completiondate">{$task->completiondate}</td>
            <td class="plantasktitle">{$task->title}</td>
            <td class="plantaskdescription">{$task->description|clean_html|safe}</td>
            <td class="plantasktags">{if $task->tags}{list_tags owner=$task->owner tags=$task->tags}{/if}</td>
            {if $task->completed == 1}
                <td class="completed"><img src="{$WWWROOT}theme/raw/static/images/success_small.png" alt="{str tag=completed section=artefact.plans}" /></td>
            {else}
                <td><span class="accessible-hidden">{str tag=incomplete section=artefact.plans}</span></td>
            {/if}

    {/if}
            <td class="buttonscell btns2 planscontrols">
                <a href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->task}" title="{str tag=edit}">
                    <img src="{theme_image_url filename='btn_edit'}" alt="{str(tag=editspecific arg1=$task->title)|escape:html|safe}">
                </a>
                <a href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->task}" title="{str tag=delete}">
                    <img src="{theme_image_url filename='btn_deleteremove'}" alt="{str(tag=deletespecific arg1=$task->title)|escape:html|safe}">
                </a>
            </td>
        </tr>
{/foreach}
