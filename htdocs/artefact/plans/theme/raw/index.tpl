{auto_escape on}
{include file="header.tpl"}
<div id="planswrap">
    <div class="rbuttons">
        <a class="btn btn-add" href="{$WWWROOT}artefact/plans/new/">{str section="artefact.plans" tag="newplan"}</a>
    </div>
{if !$plans->data}
    <div class="message">{$strnoplanssaddone|safe}</div>
{else}
<table id="planslist" class="tablerenderer">
    <colgroup width="25%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="plansdate">{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='description' section='artefact.plans'}</th>
            <th>{str tag='completed' section='artefact.plans'}</th>
            <th class="planscontrols"></th>
            <th class="planscontrols"></th>
        </tr>
    </thead>
    <tbody>
        {$plans->tablerows|safe}
    </tbody>
</table>
   {$plans->pagination|safe}
{/if}
</div>
{include file="footer.tpl"}
{/auto_escape}
