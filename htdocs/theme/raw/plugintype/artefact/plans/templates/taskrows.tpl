{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <div class="task-item plan_incomplete list-group-item {if $task->description}list-group-item-default{/if}">

            {if $task->description}<a class="link-block collapsed" href="#expand-task-{$task->id}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}">{/if}

                <span class="overdue-task">
                    <span class="icon icon-times text-danger icon-lg prs"></span>
                    <span class="text-danger">{$task->title}</span> -
                    <span class="text-small text-lighttone">
                        {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                    </span>
                    {if $task->description}
                    <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
                    {/if}
                </span>
            {if $task->description}</a>{/if}

            {if $task->description}
            <div class="collapse" id="expand-task-{$task->id}">
                 <div class="panel-body pb0">
                    {$task->description|clean_html|safe}
                    {if $task->tags}
                    <p class="tags">
                        <strong>{str tag=tags}:</strong> {list_tags owner=$task->owner tags=$task->tags}
                    </p>
                    {/if}
                </div>
            </div>
            {/if}
        </div>
    {else}
        <div class="task-item list-group-item {if $task->description}list-group-item-default{/if}">

            {if $task->description}<a class="link-block collapsed" href="#expand-task-{$task->id}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}">{/if}

                <span class="complete-task">
                    {if $task->completed == 1}
                        <span class="icon icon-check-square-o icon-lg text-success prs"></span>
                        <span class="sr-only">{str tag=completed section=artefact.plans}</span>
                    {else}
                        <span class="icon-square-o icon icon-lg text-lighttone prs"></span>
                        <span class="sr-only">{str tag=incomplete section=artefact.plans}</span>
                    {/if}

                    <span class="text-default">{$task->title}</span> -
                    <span class="text-lighttone text-small">
                        {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                    </span>

                    {if $task->description}
                    <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
                    {/if}
                </span>

            {if $task->description}</a>{/if}

            {if $task->description}
            <div class="collapse" id="expand-task-{$task->id}">
                <div class="panel-body pb0">

                    {$task->description|clean_html|safe}

                    {if $task->tags}
                    <p class="tags">
                        <strong>{str tag=tags}:</strong> {list_tags owner=$task->owner tags=$task->tags}
                    </p>
                    {/if}

                </div>
            </div>
            {/if}
        </div>
    {/if}
{/foreach}
