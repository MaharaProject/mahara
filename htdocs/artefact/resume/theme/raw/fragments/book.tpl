<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='book' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addbook'}
{/if}
</legend>{/if}
<table id="booklist{$suffix}" class="tablerenderer resumefour resumecomposite">
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
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-head">
            <td class="toggle">{$row->date}</td>
            <td>{$row->title}</td>
        </tr>
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-body">
        	<td colspan="2">{$row->description}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
{if $controls}
<div>
    <div id="bookform" class="hidden">{$compositeforms.book|safe}</div>
    <button id="addbookbutton" class="cancel" onclick="toggleCompositeForm('book');">{str tag='add'}</button>
</div>
{/if}
</fieldset>
