<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='certification' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addcertification'}
{/if}
</legend>{/if}
<table id="certificationlist{$suffix}" class="tablerenderer resumefour resumecomposite">
    <colgroup width="25%" span="1"></colgroup>
    <thead>
        <tr>
            <th class="resumedate">{str tag='date' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
            {if $controls}
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            <th class="resumecontrols"></th>
            {/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r1'}">
            <td>{$row->date|escape}</td>
            <td><div class="jstitle">{$row->title|escape}</div><div class="jsdescription">{$row->description|escape}</div></td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if $controls}
<div>
    <div id="certificationform" class="hidden">{$compositeforms.certification}</div>
    <button id="addcertificationbutton" class="cancel" onclick="toggleCompositeForm('certification');">{str tag='add'}</button>
</div>
{/if}
</fieldset>
