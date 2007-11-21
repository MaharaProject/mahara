{if !$hidetitle}<h3>{str tag='membership' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addmembership'}
{/if}
</h3>{/if}
<table id="membershiplist_{$suffix}" class="tablerenderer hidden resumefive">
    <thead>
        <tr>
            <th class="resumedate">{str tag='startdate' section='artefact.resume'}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
            {if $controls}
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
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
