{include file="header.tpl"}
<div id="planswrap">
    <div class="text-right">
        <a class="btn btn-primary" href="{$WWWROOT}artefact/plans/new/task.php">{str section="artefact.plans" tag="newtask"}</a>
    </div>
{if !$tasks.data}
    <div class="metadata">{$strnotasksaddone|safe}</div>
{else}
<table id="planslist">
    <thead>
        <tr>
            <th class="completiondate">{str tag='completiondate' section='artefact.plans'}</th>
            <th class="plantitle">{str tag='title' section='artefact.plans'}</th>
            <th class="plandescription">{str tag='description' section='artefact.plans'}</th>
            <th class="planscontrols"></th>
            <th class="planscontrols"></th>
            <th class="planscontrols"></th>
        </tr>
    </thead>
    <tbody>
        {$tasks.tablerows|safe}
    </tbody>
</table>
   {$tasks.pagination|safe}
{/if}
</div>
{include file="footer.tpl"}
