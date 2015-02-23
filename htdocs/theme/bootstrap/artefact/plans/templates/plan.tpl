{include file="header.tpl"}
<div class="text-right btn-top-right">
    <a class="btn btn-success" href="{$WWWROOT}artefact/plans/new.php?id={$plan}">{str section="artefact.plans" tag="newtask"}</a>
</div>
<div id="planswrap" class="plan-wrapper">
    {if $tags}
    <p class="tags">
        <strong>{str tag=tags}:</strong> 
        {list_tags owner=$owner tags=$tags}
    </p>
    {/if}
{if !$tasks.data}
    <div>{$planstasksdescription}</div>
    <div class="message">{$strnotasksaddone|safe}</div>
{else}
<div class="table-responsive">
<table id="taskslist" class="fullwidth listing table table-striped">
    <thead>
        <tr>
            <th>{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='description' section='artefact.plans'}</th>
            <th class="center">{str tag='completed' section='artefact.plans'}</th>
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
