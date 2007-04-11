<h3>{str tag='book' section='artefact.resume'}</h3>
<table id="booklist" class="tablerenderer hidden">
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
    <button id="addbookbutton" onclick="toggleCompositeForm('book');">{str tag='add'}</button>
    <div id="bookform" class="hiddenStructure">{$compositeforms.book}</div>
</div>
{/if}
