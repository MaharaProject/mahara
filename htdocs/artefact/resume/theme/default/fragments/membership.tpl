<h3>{str tag='membership' section='artefact.resume'}</h3>
<table id="membershiplist" class="tablerenderer hidden">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
            {if $controls}
            <th></th>
            <th></th>
            {/if}
        </tr>
    </thead>
</table>
{if $controls}
<div>
    <button id="addmembershipbutton" onclick="toggleCompositeForm('membership');">{str tag='add'}</button>
    <div id="membershipform" class="hiddenStructure">{$compositeforms.membership}</div>
</div>
{/if}
