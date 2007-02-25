<h3>{str tag='educationhistory' section='artefact.resume'}</h3>
<table id="educationhistorylist">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='qualification' section='artefact.resume'}</th>
            {if $controls}
            <th></th>
            <th></th>
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
