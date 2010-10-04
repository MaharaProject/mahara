{auto_escape on}
{include file="header.tpl"}
<div id="planswrap">
    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}artefact/plans/new/task.php">{str section="artefact.plans" tag="newtask"}</a>
    </div>
{if !$tasks.data}
    <div class="message">{$strnotasksaddone|safe}</div>
{else}
<table id="planslist">
    <thead>
        <tr>
            <th>{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='description' section='artefact.plans'}</th>
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
{/auto_escape}
