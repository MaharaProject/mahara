{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <li class="task-item plan_incomplete list-group-item {if $task->description}list-group-item-default{/if}">

            {if $task->description}<a class="link-block collapsed" href="#expand-task-{$task->id}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}">{/if}

                <span class="text-danger">
                    <span class="icon icon-times icon-lg prs"></span>
                    {$task->title} -
                    <span class="metadata">
                        {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                    </span>
                    {if $task->description}
                    <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
                    {/if}
                </span>
            {if $task->description}</a>{/if}

            {if $task->description}
            <div class="text-small collapse" id="expand-task-{$task->id}">
                 <div class="panel-body">
                    {$task->description|clean_html|safe}
                    {if $task->tags}
                    <p class="tags">
                        <strong>{str tag=tags}:</strong> {list_tags owner=$task->owner tags=$task->tags}
                    </p>
                    {/if}
                </div>
            </div>
            {/if}
        </li>
    {else}
        <li class="task-item list-group-item {if $task->description}list-group-item-default{/if}">

            {if $task->description}<a class="link-block collapsed" href="#expand-task-{$task->id}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}">{/if}

                <span class=" {if $task->completed == 1}text-success{/if} ">
                    {if $task->completed == 1}
                        <span class="icon icon-check-square-o icon-lg text-success prs"></span>
                        <span class="sr-only">{str tag=completed section=artefact.plans}</span>
                    {else}
                        <span class="icon-square-o icon icon-lg text-light prs icon-placeholder"></span>
                        <span class="sr-only">{str tag=incomplete section=artefact.plans}</span>
                    {/if}

                    {$task->title} - 
                    <span class="metadata">
                        {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                    </span>

                    {if $task->description}
                    <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
                    {/if}
                </span>

            {if $task->description}</a>{/if}

            {if $task->description}
            <div class="text-small collapse" id="expand-task-{$task->id}">
                <div class="panel-body">
                
                    {$task->description|clean_html|safe}

                    {if $task->tags}
                    <p class="tags">
                        strong>{str tag=tags}:</strong> {list_tags owner=$task->owner tags=$task->tags}
                    </p>
                    {/if}

                </div>
            </div>
            {/if}

           
           
        </li>
    {/if}
{/foreach}