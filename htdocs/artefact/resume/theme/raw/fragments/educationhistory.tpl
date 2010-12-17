<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='educationhistory' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addeducationhistory'}
{/if}
</legend>{/if}
<table id="educationhistorylist{$suffix}" class="tablerenderer resumefive resumecomposite">
    <thead>
        <tr>
            {if $controls}<th class="resumecontrols"></th>{/if}
            <th class="resumedate">{str tag='startdate' section='artefact.resume'}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='qualification' section='artefact.resume'}</th>
            {if $controls}<th class="resumecontrols"></th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-head">
            {if $controls}<td class="buttonscell"></td>{/if}
            <td class="toggle">{$row->startdate}</td>
            <td>{$row->enddate}</td>
            <td>{$row->qualification}</td>
            {if $controls}<td class="buttonscell"></td>{/if}
        </tr>
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-body">
            {if $controls}<td class="buttonscell"></td>{/if}
        	<td colspan="3">{$row->qualdescription}</td>
            {if $controls}<td class="buttonscell"></td>{/if}
        </tr>
        {/foreach}
    </tbody>
</table>
{if $controls}
<div>
    <div id="educationhistoryform" class="hidden">{$compositeforms.educationhistory|safe}</div>
    <button id="addeducationhistorybutton" class="cancel" onclick="toggleCompositeForm('educationhistory');">{str tag='add'}</button>
</div>
{/if}
</fieldset>
