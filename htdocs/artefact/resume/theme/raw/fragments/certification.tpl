<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='certification' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addcertification'}
{/if}
</legend>{/if}
<table id="certificationlist{$suffix}" class="tablerenderer resumefour resumecomposite fullwidth">
    <thead>
        <tr>
            {if $controls}<th class="resumecontrols"></th>{/if}
            <th class="resumedate">{str tag='date' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
            <th class="resumeattachments center"><img src="{theme_url filename="images/attachment.png"}" title="{str tag=Attachments section=artefact.resume}" /></th>
            {if $controls}<th class="resumecontrols"></th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-head">
            {if $controls}<td class="buttonscell"></td>{/if}
            <td class="toggle">{$row->date}</td>
            <td>{$row->title}</td>
            <td class="center">{$row->clipcount}</td>
            {if $controls}<td class="buttonscell"></td>{/if}
        </tr>
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-body">
            {if $controls}<td class="buttonscell"></td>{/if}
            <td colspan="3"><div class="compositedesc">{$row->description}</div>
            {if $row->attachments}
            <table class="cb attachments fullwidth">
                <tbody>
                    <tr><th colspan="2">{str tag='attachedfiles' section='artefact.blog'}:</th></tr>
                    {foreach from=$row->attachments item=item}
                    <tr class="{cycle values='r0,r1'}">
                        {if $icons}<td class="iconcell"><img src="{$item->iconpath}" alt=""></td>{/if}
                        <td><a href="{$item->viewpath}">{$item->title}</a> ({$item->size}) - <strong><a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></strong>
                        <br>{$item->description}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            {/if}
            </td>
            {if $controls}<td class="buttonscell"></td>{/if}
        </tr>
        {/foreach}
    </tbody>
</table>
{if $controls}
<div>
    <div id="certificationform" class="hidden">{$compositeforms.certification|safe}</div>
    <button id="addcertificationbutton" class="cancel" onclick="toggleCompositeForm('certification');">{str tag='add'}</button>
</div>
{/if}
{if $license}
<div class="resumelicense">
{$license|safe}
</div>
{/if}
</fieldset>
