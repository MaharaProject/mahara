{if !$hidetitle}<h3>{str tag='certification' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addcertification'}
{/if}
</h3>{/if}
<table id="certificationlist_{$suffix}" class="tablerenderer hidden resumefour">
    <thead>
        <tr>
            <th class="resumedate">{str tag='date' section='artefact.resume'}</th>
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
    <button id="addcertificationbutton" onclick="toggleCompositeForm('certification');">{str tag='add'}</button>
    <div id="certificationform" class="hiddenStructure">{$compositeforms.certification}</div>
</div>
{/if}
