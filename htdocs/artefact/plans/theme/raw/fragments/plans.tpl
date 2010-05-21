{auto_escape off}
<fieldset>{if !$hidetitle}<legend class="plansh3">{str tag='plans' section='artefact.plans'}
{if $controls} 
    {contextualhelp plugintype='artefact' pluginname='plans' section='plans'}
{/if}
</legend>{/if}
{if $rows}
<table id="planslist" class="tablerenderer">
    <colgroup width="25%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="plansdate">{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='completed' section='artefact.plans'}</th>
            {if $controls}
            <th class="planscontrols"></th>
            <th class="planscontrols"></th>
            {/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r1'}">
            <td>{$row->completiondate|escape}</td>
            <td>{$row->title|escape}<div>{$row->description|escape}</div></td>
            {if $row->completed == 1}<td><div class="completed"><img src="/artefact/plans/theme/raw/static/images/success.gif" alt="" /></div></td>{else}<td></td>{/if}
            {if $controls}
            <td><a href="/artefact/plans/editplan.php?id={$row->id}&amp;artefact={$row->artefact}">Edit</a></td>
            <td><a href="/artefact/plans/deleteplan.php?artefact={$row->artefact}">Delete</a></td>
            {/if}
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
{if $controls}
<div>
    <div id="plansform">{$plansform}</div>
</div>
{/if}
</fieldset>
{/auto_escape}
