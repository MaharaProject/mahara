{if $group}
    {assign var="groupurlquery" value="group=$group&"}
{/if}

{foreach from=$tasks.data item=task}
    <tr class="task{if $task->outcomeiscurrentlysubmitted} submitted{/if}">

        {*Column 1 - Completion checkbox : Only visible if task is editable, is no template and no selection task *}
        {if $canedit && !$task->template && !$tasks.selectiontasks}
            {if $task->completed == -1}
                <td class="incomplete task-status">
                    <span class="icon icon-times icon-lg text-danger" role="presentation" aria-hidden="true"></span>
                    <span class="visually-hidden">{str tag=overdue section=artefact.plans}</span>
                </td>
            {elseif $task->completed == 1}
                <td class="completed task-status">
                    <span class="completed-checkbox icon icon-regular icon-check-square icon-lg text-success" data-taskid="{$task->id}" data-completed="{$task->completed}" role="presentation" aria-hidden="true"></span>
                    <span class="visually-hidden">{str tag=completed section=artefact.plans}</span>
                </td>
            {else}
                <td class="incomplete task-status">
                    <span class="completed-checkbox icon icon-regular icon-square icon-lg" data-taskid="{$task->id}" data-completed="{$task->completed}" role="presentation" aria-hidden="true"></span>
                    <span class="visually-hidden">{str tag=completed section=artefact.plans}</span>
                </td>
            {/if}
        {/if}

        <td>
            <div class="plantasktitle">{$task->title}</div>
        </td>

        <td class="completiondate">{$task->completiondate}</td>

        <td class="plantaskdescription">
            {$task->description|clean_html|safe}
            {if $task->tags}<span class="text-small text-midtone">{str tag=tags}: {list_tags owner=$task->owner tags=$task->tags}</span>{/if}
        </td>

        <td class="planscontrols text-end">
            <div class="text-nowrap">
                <div class="btn-group btn-tasks">
                    {if $task->taskview}
                        <button data-url="{$WWWROOT}view/view.php?id={$task->taskview}" data-ignore="true" type="button" class="btn btn-secondary btn-sm btn-view" title="{$showassignedview}">
                            <span class="icon icon-info" role="presentation" aria-hidden="true"></span>
                        </button>
                    {/if}

                    {if $task->outcomeurl}
                        <button data-url="{$task->outcomeurl}" data-ignore="true" class="btn btn-secondary btn-sm btn-outcome" type="button" title="{$editassignedoutcome}" {if $task->sourceoutcomeurl}data-sourceoutcomeurl="{$task->sourceoutcomeurl}"{/if}>
                            <span class="icon icon-file" role="presentation" aria-hidden="true"></span>
                        </button>
                    {/if}

                    {if !$task->outcomeiscurrentlysubmitted}
                        {if $task->outcomesubmissionurl}
                            <button data-url="{$task->outcomesubmissionurl}" type="submit" title="{$submitassignedoutcome}" class="btn btn-secondary btn-sm">
                                <span class="icon icon-file-upload" role="presentation" aria-hidden="true"></span>
                                <span class="visually-hidden">{$submitassignedoutcome}</span>
                            </button>
                        {/if}

                        {if $canedit}
                            <button data-url="{$WWWROOT}artefact/plans/task/edit.php?{$groupurlquery}id={$task->task}" type="button" title="{str tag=edit}" class="btn btn-secondary btn-sm">
                                <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                                <span class="visually-hidden">{str(tag=editspecific arg1=$task->title)|escape:html|safe}</span>
                            </button>
                            <button data-url="{$WWWROOT}artefact/plans/task/delete.php?{$groupurlquery}id={$task->task}" type="button" title="{str tag=delete}" class="btn btn-secondary btn-sm">
                                <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                                <span class="visually-hidden">{str(tag=deletespecific arg1=$task->title)|escape:html|safe}</span>
                            </button>
                        {/if}
                    {/if}
                </div>
                <div class="btn-group">
                    {if !$task->outcomeiscurrentlysubmitted && !$canedit && $tasks.selectiontasks}
                        {if $task->isActiveRootGroupTask}
                            {assign var="checked" value='checked'}
                        {else}
                            {assign var="checked" value=''}
                        {/if}
                        {if $task->outcomeiscurrentlysubmitted}
                            {assign var="prepareforeventsclass" value=''}
                            {assign var="disabled" value='disabled'}
                        {else}
                            {assign var="prepareforeventsclass" value='btn-toggle '}
                            {assign var="disabled" value=''}
                        {/if}
                        <div class="form-switch {$prepareforeventsclass}" data-taskid="{$task->id}" data-chosen={$task->isActiveRootGroupTask}>
                            <div class="switch">
                                <input type="checkbox" {$disabled} {$checked} class="switchbox" tabindex="0" id="tasktoggle" >
                                <label class="switch-label" for="tasktoggle" aria-hidden="true">
                                    <span class="switch-inner"></span>
                                    <span class="switch-indicator"></span>
                                    <span class="state-label on">{str tag=switchbox.yes section=pieforms}</span>
                                    <span class="state-label off">{str tag=switchbox.no section=pieforms}</span>
                                </label>
                            </div>
                        </div>
                    {elseif $task->outcomeiscurrentlysubmitted}
                        <div class="text-end">
                            <span>{str tag=outcomeiscurrentlysubmitted section=artefact.plans}</span>
                        </div>
                    {/if}
                </div>
            </div>
        </td>
    </tr>
{/foreach}
