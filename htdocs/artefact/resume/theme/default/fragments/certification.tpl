<h3>{str tag='certification' section='artefact.resume'}</h3>
<table id="certificationlist" class="tablerenderer hidden">
    <thead>
        <tr>
            <th>{str tag='date' section='artefact.resume'}</th>
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
    <button id="addcertificationbutton" onclick="toggleCompositeForm('certification');">{str tag='add'}</button>
    <div id="certificationform" class="hiddenStructure">{$compositeforms.certification}</div>
</div>
{/if}
