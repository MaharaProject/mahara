<fieldset>{if !$hidetitle}<legend class="resumeh3">{str tag='certification' section='artefact.resume'}
{if $controls}
    {contextualhelp plugintype='artefact' pluginname='resume' section='addcertification'}
{/if}
</legend>{/if}
<table id="certificationlist{$suffix}" class="tablerenderer resumefour resumecomposite fullwidth">
    <thead>
        <tr>
            {if $controls}<th class="resumecontrols">
                <span class="accessible-hidden">{str tag=move}</span>
            </th>{/if}
            <th>{str tag='title' section='artefact.resume'}</th>
            <th class="resumeattachments center"><img src="{theme_image_url filename="attachment"}" title="{str tag=Attachments section=artefact.resume}" alt="{str tag=Attachments section=artefact.resume}" /></th>
            {if $controls}<th class="resumecontrols">
                <span class="accessible-hidden">{str tag=edit}</span>
            </th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r1'}">
            {if $controls}<td class="buttonscell"></td>{/if}
            <td>
                <div class="expandable-head">
                    {if $row->description || $row->attachments}<a class="toggle textonly" href="#">{else}<strong>{/if}
                        {$row->title}
                    {if $row->description || $row->attachments}</a>{else}</strong>{/if}
                    <div>{$row->date}</div>
                </div>
                <div class="expandable-body">
                    <div class="compositedesc">{$row->description}</div>
                    {if $row->attachments}
                    <table class="cb attachments fullwidth">
                        <thead class="expandable-head">
                            <tr>
                                <th colspan="2"><a class="toggle" href="#">{str tag='attachedfiles' section='artefact.blog'}</a></th>
                            </tr>
                        </thead>
                        <tbody class="expandable-body">
                            {foreach from=$row->attachments item=item}
                            <tr>
                                {if $icons}<td class="iconcell"><img src="{$item->iconpath}" alt=""></td>{/if}
                                <td><a href="{$item->viewpath}">{$item->title}</a> ({$item->size}) - <strong><a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></strong>
                                <br>{$item->description}</td>
                            </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    {/if}
                </div>
            </td>
            <td class="center">{$row->clipcount}</td>
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
<script type="application/javascript">
setupExpanders(jQuery('#certificationlist{$suffix}'));
</script>
