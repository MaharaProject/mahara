{include file="header.tpl"}
<div id="planswrap">
    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}artefact/plans/new.php?id={$plan}">{str section="artefact.plans" tag="newtask"}</a>
    </div>
    {if $tags}<p class="tags s"><strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}</p>{/if}
{if !$tasks.data}
    <div>{$planstasksdescription}</div>
    <div class="message">{$strnotasksaddone|safe}</div>
{else}
<table id="taskslist" class="fullwidth listing">
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
   {$tasks.pagination|safe}
{/if}
</div>
{include file="footer.tpl"}
