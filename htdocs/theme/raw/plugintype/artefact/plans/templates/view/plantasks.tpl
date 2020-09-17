{foreach from=$tasks.data item=task}
    <div class="task-item list-group-item{if $task->completed == -1} plan_incomplete list-group-item-danger{/if}">
        {if $editing}
        <div class="float-right btn-group">
            <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/task/edit.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='editthistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-pencil-alt text-default"></span></a>
            <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/task/delete.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='deletethistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-trash-alt text-danger"></span></a>
        </div>
        {/if}
        <span class="{if $task->completed == -1}overdue-task{else}complete-task{/if}">
            {if $task->completed == -1}
                <span class="icon icon-times text-danger icon-lg left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="sr-only">{str tag=overdue section=artefact.plans}</span>
            {elseif $task->completed == 1}
                <span class="icon icon-regular icon-check-square icon-lg text-success left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="sr-only">{str tag=completed section=artefact.plans}</span>
            {else}
                <span class="icon-regular icon-square icon icon-lg text-midtone left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="sr-only">{str tag=incomplete section=artefact.plans}</span>
            {/if}
            <div class="plan-task-heading">
                {if $task->description || $task->tags}
                <a class="{if !$options.pdfexport}collapsed{/if}" href="#expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}" data-toggle="collapse" aria-expanded="{if !$options.pdfexport}true{else}false{/if}" aria-controls="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
                {/if}
                    <h4 class="list-group-item-heading {if $task->completed == -1}text-danger{else}text-default{/if}">{$task->title}</h4>
                {if $task->description || $task->tags}
                    <span class="icon icon-chevron-down right collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
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
                    <strong>{str tag=tags}:</strong> {list_tags owner=$task->owner tags=$task->tags view=$view}
                </div>
            {/if}
        </div>
        {/if}
    </div>
{/foreach}
