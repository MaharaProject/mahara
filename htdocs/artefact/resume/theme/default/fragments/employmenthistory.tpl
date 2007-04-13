<h3>{str tag='employmenthistory' section='artefact.resume'}
{contextualhelp plugintype='artefact' pluginname='resume' section='addemploymenthistory'}</h3>
<table id="employmenthistorylist" class="tablerenderer hidden">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='position' section='artefact.resume'}</th>
            {if $controls}
            <th></th>
            <th></th>
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
