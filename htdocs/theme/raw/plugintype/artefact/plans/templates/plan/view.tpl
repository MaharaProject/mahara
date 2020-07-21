{include file="header.tpl"}
{if $canedit}
    <div class="btn-top-right btn-group btn-group-top">
        <a class="btn btn-secondary settings" href="{$newtasklink}">
            <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
            {str section="artefact.plans" tag="newtask"}
        </a>
    </div>
{/if}
<div id="planswrap" class="plan-wrapper view-container">
{if $tags}
    <div class="tags">
        <strong>{str tag=tags}:</strong>
        {list_tags owner=$owner tags=$tags}
    </div>
{/if}
{if !$tasks.data}
    <div class="no-results">
        {if $canedit}
            {$planstasksdescription}
        {/if}
        <p>{$strnotasks|safe}</p>
    </div>
{else}
    <div class="table-responsive">
        <table id="taskslist" class="task-listing table table-striped">
            <thead>
                <tr>
                    {if $canedit && !$task[0]->template && !$tasks.selectiontasks}
                        <th class="task-status">{str tag='completed' section='artefact.plans'}</th>
                    {/if}
                    <th>{str tag='title' section='artefact.plans'}</th>
                    <th>{str tag='completiondate' section='artefact.plans'}</th>
                    <th>{str tag='description' section='artefact.plans'}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                {$tasks.tablerows|safe}
            </tbody>
        </table>
    </div>
   {$tasks.pagination|safe}
{/if}
</div>
{include file="pagemodal.tpl"}
{include file="footer.tpl"}
