{if !$hidetitle}<h3>{str tag='educationhistory' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addeducationhistory'}
{/if}
</h3>{/if}
<table id="educationhistorylist_{$suffix}" class="tablerenderer hidden resumefive">
    <thead>
        <tr>
            <th class="resumedate">{str tag='startdate' section='artefact.resume'}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='qualification' section='artefact.resume'}</th>
            {if $controls}
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            {/if}
        </tr>
    </thead>
</table>
{if $controls}
<div>
    <button id="addeducationhistorybutton" onclick="toggleCompositeForm('educationhistory');">{str tag='add'}</button>
    <div id="educationhistoryform" class="hiddenStructure">{$compositeforms.educationhistory}</div>
</div>
{/if}
