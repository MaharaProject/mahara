{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <div class="task-item plan_incomplete list-group-item {if $task->description || $task->tags}list-group-item-default{/if}">
            {if $editing}
            <div class="float-right btn-group">
                <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='editthistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-pencil text-default"></span></a>
                <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='deletethistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-trash text-danger"></span></a>
            </div>
            {/if}
            {if $task->description || $task->tags} <a class="link-block collapsed" href="#expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">{/if}
                <span class="overdue-task">
                    <div class="collapse-inline">
                        <span class="icon icon-times text-danger icon-lg left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                        <span class="text-danger">{$task->title}</span> -
                        <span class="text-small text-midtone">
                            {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                        </span>
                    </div>

                    {if $task->description || $task->tags}
                    <span class="icon icon-chevron-down right collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                    {/if}
                </span>
            {if $task->description || $task->tags}</a>{/if}

            {if $task->description || $task->tags}
            <div class="collapse" id="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
                 <div class="card-body">
                    {if $task->description}
                        {$task->description|clean_html|safe}
                    {/if}
                    {if $task->tags}
                    <div class="tags">
                        <strong>{str tag=tags}:</strong> {list_tags owner=$task->owner tags=$task->tags view=$view}
                    </div>
                    {/if}
                </div>
            </div>
            {/if}
        </div>
    {else}
        <div class="task-item list-group-item {if $task->description || $task->tags}list-group-item-default{/if}">
            {if $editing}
            <div class="float-right btn-group">
                <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/edit/task.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='editthistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-pencil text-default"></span></a>
                <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/delete/task.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='deletethistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-trash text-danger"></span></a>
            </div>
            {/if}
            {if $task->description || $task->tags}<a class="link-block collapsed" href="#expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">{/if}
                <span class="complete-task">
                    <div class="collapse-inline">
                        {if $task->completed == 1}
                            <span class="icon icon-check-square-o icon-lg text-success left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                            <span class="sr-only">{str tag=completed section=artefact.plans}</span>
                        {else}
                            <span class="icon-square-o icon icon-lg text-midtone left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                            <span class="sr-only">{str tag=incomplete section=artefact.plans}</span>
                        {/if}
                        <span class="text-default">{$task->title}</span> -
                        <span class="text-midtone text-small">
                            {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                        </span>
                    </div>

                    {if $task->description || $task->tags}
                    <span class="icon icon-chevron-down right collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                    {/if}
                </span>

            {if $task->description || $task->tags}</a>{/if}

            {if $task->description || $task->tags}
            <div class="collapse" id="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
                 <div class="card-body">
                    {if $task->description}
                        {$task->description|clean_html|safe}
                    {/if}
                    {if $task->tags}
                    <div class="tags">
                        <strong>{str tag=tags}:</strong> {list_tags owner=$task->owner tags=$task->tags view=$view}
                    </div>
                    {/if}
                </div>
            </div>
            {/if}
        </div>
    {/if}
{/foreach}
