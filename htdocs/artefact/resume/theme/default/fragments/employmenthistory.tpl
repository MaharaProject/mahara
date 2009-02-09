{if !$hidetitle}<h3>{str tag='employmenthistory' section='artefact.resume'}
{if $controls} 
    {contextualhelp plugintype='artefact' pluginname='resume' section='addemploymenthistory'}
{/if}
</h3>{/if}
<table id="employmenthistorylist{$suffix}" class="tablerenderer resumefive resumecomposite">
    <colgroup width="25%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="resumedate">{str tag='startdate' section='artefact.resume'}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='position' section='artefact.resume'}</th>
            {if $controls}
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            {/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="r{cycle values=0,1}">
            <td>{$row->startdate|escape}</td>
            <td>{$row->enddate|escape}</td>
            <td><div class="jstitle">{$row->jobtitle|escape}: {$row->employer|escape}</div>
            <div class="jsdescription">{$row->positiondescription|escape}</div></td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if $controls}
<div>
    <button id="addemploymenthistorybutton" onclick="toggleCompositeForm('employmenthistory');">{str tag='add'}</button>
    <div id="employmenthistoryform" class="hidden">{$compositeforms.employmenthistory}</div>
</div>
{/if}
