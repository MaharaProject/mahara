{if !$hidetitle}<h3>{str tag='employmenthistory' section='artefact.resume'}
{if $controls} 
    {contextualhelp plugintype='artefact' pluginname='resume' section='addemploymenthistory'}
{/if}
</h3>{/if}
<table id="employmenthistorylist_{$suffix}" class="tablerenderer hidden resumefive">
    <thead>
        <tr>
            <th class="resumedate">{str tag='startdate' section='artefact.resume'}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='position' section='artefact.resume'}</th>
            {if $controls}
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            {/if}
        </tr>
    </thead>
</table>
{if $controls}
<div>
    <button id="addemploymenthistorybutton" onclick="toggleCompositeForm('employmenthistory');">{str tag='add'}</button>
    <div id="employmenthistoryform" class="hiddenStructure">{$compositeforms.employmenthistory}</div>
</div>
{/if}
