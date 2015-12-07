{include file="header.tpl"}
<div class="btn-top-right btn-group btn-group-top">
    <a class="btn btn-default settings" href="{$WWWROOT}artefact/plans/new.php?id={$plan}">
        <span class="icon icon-lg icon-plus left" role="presentation" aria-hidden="true"></span>
        {str section="artefact.plans" tag="newtask"}
    </a>
</div>
<div id="planswrap" class="plan-wrapper view-container">
    {if $tags}
    <p class="tags">
        <strong>{str tag=tags}:</strong>
        {list_tags owner=$owner tags=$tags}
    </p>
    {/if}
{if !$tasks.data}
    <div class="no-results">
        {$planstasksdescription}
        <p class="metadata">{$strnotasksaddone|safe}</p>
    </div>
{else}
<div class="table-responsive">
<table id="taskslist" class="listing table table-striped text-small">
    <thead>
        <tr>
            <th>{str tag='completed' section='artefact.plans'}</th>
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
{include file="footer.tpl"}
