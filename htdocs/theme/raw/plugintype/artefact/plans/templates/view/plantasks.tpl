{foreach from=$tasks.data item=task}
    {if $task->completed == -1}
        <div class="task-item plan_incomplete list-group-item">
            {if $editing}
                <div class="float-right btn-group">
                    <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/task/edit.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='editthistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-pencil-alt text-default"></span></a>
                    <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/task/delete.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='deletethistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-trash-alt text-danger"></span></a>
                </div>
            {/if}
            {if $task->description || $task->tags}
            <a class="outer-link collapsed" href="#expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
                <span class="sr-only">{$task->title}</span>
                <span class="icon icon-chevron-down right collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
            </a>
            {/if}
            <span class="overdue-task">
                <span class="icon icon-times text-danger icon-lg left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <div class="plan-task-heading">
                    <span class="text-danger">{$task->title}</span>
                    {if $task->completiondate}
                    <br />
                    <span class="text-small text-midtone">
                        {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                    </span>
                    {/if}
                </div>

                {if $task->description || $task->tags}
                <div class="collapse plan-task-detail" id="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
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
            </span>
        </div>
    {else}
        <div class="task-item list-group-item">
            {if $editing}
                <div class="float-right btn-group">
                    <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/task/edit.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='editthistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-pencil-alt text-default"></span></a>
                    <a class="btn btn-secondary btn-sm" href="{$WWWROOT}artefact/plans/task/delete.php?id={$task->id}{if $view}&view={$view}{/if}" title="{str tag='deletethistask' section='artefact.plans' arg1=$task->title}"><span class="icon icon-trash-alt text-danger"></span></a>
                </div>
            {/if}
            {if $task->description || $task->tags}
            <a class="outer-link collapsed" href="#expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}" data-toggle="collapse" aria-expanded="false" aria-controls="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
                <span class="sr-only">{$task->title}</span>
                <span class="icon icon-chevron-down right collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
            </a>
            {/if}
            <span class="complete-task">
            {if $task->completed == 1}
                <span class="icon icon-regular icon-check-square icon-lg text-success left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="sr-only">{str tag=completed section=artefact.plans}</span>
            {else}
                <span class="icon-regular icon-square icon icon-lg text-midtone left task{$task->id}{if $editing || $canedit} plan-task-icon{/if}" role="presentation" aria-hidden="true" data-task="{$task->id}"></span>
                <span class="sr-only">{str tag=incomplete section=artefact.plans}</span>
            {/if}
                <div class="plan-task-heading">
                    <span class="text-default">{$task->title}</span>
                    {if $task->completiondate}
                    <br />
                    <span class="text-midtone text-small">
                        {str tag='completiondate' section='artefact.plans'}: {$task->completiondate}
                    </span>
                    {/if}
                </div>
            </span>
            {if $task->description || $task->tags}
            <div class="collapse plan-task-detail" id="expand-task-{$task->id}{if $block}-{$block}{/if}{if $versioning}-{$versioning->version}{/if}">
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
    {/if}
{/foreach}
