{foreach from=$tasks.data item=task}
    <div class="task-item list-group-item{if $task->completed == -1} plan_incomplete{/if}">
        {if $editing}
        <div class="float-end btn-group btn-group-top">
            <button class="btn btn-secondary btn-sm" data-url="{$WWWROOT}artefact/plans/task/edit.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='editthistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-pencil-alt text-default"></span></button>
            <button class="btn btn-secondary btn-sm" data-url="{$WWWROOT}artefact/plans/task/delete.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='deletethistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-trash-alt text-danger"></span></button>
        </div>
        {/if}
        <span class="task-item-content{if $task->completed == -1} overdue-task{else} complete-task{/if}">
            {if $task->completed == -1}
                <span class="icon icon-times text-danger icon-lg left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="visually-hidden">{str tag=overdue section=artefact.plans}</span>
            {elseif $task->completed == 1}
                <span class="icon icon-regular icon-check-square icon-lg text-success left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="visually-hidden">{str tag=completed section=artefact.plans}</span>
            {else}
                <span class="icon-regular icon-square icon icon-lg text-midtone left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="visually-hidden">{str tag=incomplete section=artefact.plans}</span>
            {/if}
            <div class="plan-task-heading">
                {if $task->description || $task->tags}
                <a class="{if !$options.pdfexport}collapsed{/if}" href="#expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}" data-bs-toggle="collapse" aria-expanded="{if !$options.pdfexport}true{else}false{/if}" aria-controls="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
                {/if}
                    <h4 class="list-group-item-heading text-default">{$task->title}</h4>
                {if $task->description || $task->tags}
                    <span class="icon icon-chevron-down right collapse-indicator float-end" role="presentation" aria-hidden="true"></span>
                </a>
                {/if}
                {if $task->completiondate}
                <span class="text-small text-midtone">
                    {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                </span>
                {/if}
            </div>
        </span>
        {if $task->description || $task->tags}
        <div class="collapse{if $options.pdfexport} show{/if} plan-task-detail" id="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
            {if $task->description}
                {$task->description|clean_html|safe}
            {/if}
            {if $task->tags}
                <div class="tags text-small">
                    {str tag=tags}: {list_tags owner=$task->owner tags=$task->tags view=$view}
                </div>
            {/if}
        </div>
        {/if}
    </div>
{/foreach}
