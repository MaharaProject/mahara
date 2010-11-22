<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='membership' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addmembership'}
{/if}
</legend>{/if}
<table id="membershiplist{$suffix}" class="tablerenderer resumefive resumecomposite">
    <colgroup width="25%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="resumedate">{str tag='startdate' section='artefact.resume'}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume'}</th>
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
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-head">
            <td class="toggle">{$row->startdate}</td>
            <td>{$row->enddate}</td>
            <td>{$row->title}</td>
        </tr>
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-body">
        	<td colspan="3">{$row->description}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if $controls}
<div>
    <div id="membershipform" class="hidden">{$compositeforms.membership|safe}</div>
    <button id="addmembershipbutton" class="cancel" onclick="toggleCompositeForm('membership');">{str tag='add'}</button>
</div>
{/if}
</fieldset>
