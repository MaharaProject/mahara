{auto_escape off}
{if $rows}
<table id="planslist" class="tablerenderer">
    <colgroup width="50%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="plansdate">{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='completed' section='artefact.plans'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r1'}">
            <td>{$row->completiondate|escape}</td>
            <td>{$row->title|escape}<div>{$row->description|escape}</div></td>
            {if $row->completed == 1}<td><div class="completed"><img src="/artefact/plans/theme/raw/static/images/success.gif" alt="" /></div></td>{else}<td></td>{/if}
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
{/auto_escape}
