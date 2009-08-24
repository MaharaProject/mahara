<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='educationhistory' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addeducationhistory'}
{/if}
</legend>{/if}
<table id="educationhistorylist{$suffix}" class="tablerenderer resumefive resumecomposite">
    <colgroup width="25%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="resumedate">{str tag='startdate' section='artefact.resume'}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='qualification' section='artefact.resume'}</th>
            {if $controls}
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            {/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="r{cycle values='0,1'}">
            <td>{$row->startdate|escape}</td>
            <td>{$row->enddate|escape}</td>
            <td><div class="jstitle">{$row->qualification|escape}</div><div class="jsdescription">{$row->qualdescription|escape}</div></td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if $controls}
<div>
    <div id="educationhistoryform" class="hidden">{$compositeforms.educationhistory}</div>
    <button id="addeducationhistorybutton" class="cancel" onclick="toggleCompositeForm('educationhistory');">{str tag='add'}</button>
</div>
{/if}
</fieldset>
