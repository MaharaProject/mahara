{if !$hidetitle}<h3>{str tag='book' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addbook'}
{/if}
</h3>{/if}
<table id="booklist{$suffix}" class="tablerenderer hidden resumefour">
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
    <button id="addbookbutton" onclick="toggleCompositeForm('book');">{str tag='add'}</button>
    <div id="bookform" class="hiddenStructure">{$compositeforms.book}</div>
</div>
{/if}
