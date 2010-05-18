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
        <tr class="{cycle values='r0,r1'}">
            <td>{$row->date}</td>
            <td><div class="jstitle">{$row->title}</div><div class="jsdescription">{$row->description}</div></td>
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
