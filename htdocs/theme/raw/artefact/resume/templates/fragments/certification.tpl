<div class="panel panel-default">
    {if !$hidetitle}
    <h3 class="resumeh3 panel-heading">
        {str tag='certification' section='artefact.resume'}
        {if $controls}
        {contextualhelp plugintype='artefact' pluginname='resume' section='addcertification'}
        {/if}
    </h3>
    {/if}
    <div>
        <table id="certificationlist{$suffix}" class="tablerenderer resumefour resumecomposite fullwidth table">
            <thead>
                <tr>
                    {if $controls}<th class="resumecontrols">
                        <span class="accessible-hidden sr-only">{str tag=move}</span>
                    </th>{/if}
                    <th>{str tag='title' section='artefact.resume'}</th>
                    <th class="resumeattachments text-center">
                        <span>{str tag=Attachments section=artefact.resume}</span>
                    </th>
                    {if $controls}<th class="resumecontrols">
                        <span class="accessible-hidden sr-only">{str tag=edit}</span>
                    </th>{/if}
                </tr>
            </thead>
            <!-- This markup is rendered inside blockinstance on page -->
            <tbody>
                {foreach from=$rows item=row}
                <tr>
                    {if $controls}<td class="control-buttons"></td>{/if}
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
                            <table class="attachments table">
                                <thead>
                                    <tr>
                                        <th colspan="2">
                                            <span class="icon icon-paperclip prs"></span>
                                            <span>{str tag='attachedfiles' section='artefact.blog'}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach from=$row->attachments item=item}
                                    <tr>
                                        {if $icons}
                                        <td class="iconcell">
                                            <img src="{$item->iconpath}" alt=""></td>
                                        {/if}
                                        <td class="text-small">
                                            <a href="{$item->viewpath}">
                                                {$item->title}
                                            </a> ({$item->size}) - <strong><a href="{$item->downloadpath}">{str tag=Download section=artefact.file}</a></strong>
                                       </td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                            {/if}
                        </div>
                    </td>
                    <td class="text-center">{$row->clipcount}</td>
                    {if $controls}
                    <td class="control-buttons"></td>
                    {/if}
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    {if $controls}
    <div class="panel-footer has-form">
        <div id="certificationform" class="collapse mtl mlm" data-action='reset-on-collapse'>
            {$compositeforms.certification|safe}
        </div>

        <button id="addcertificationbutton" data-toggle="collapse" data-target="#certificationform" aria-expanded="false" aria-controls="certificationform" class="pull-right btn btn-default btn-sm collapsed expand-add-button">
            <span class="show-form">
                {str tag='add'}
                <span class="icon icon-chevron-down pls"></span>
            </span>
            <span class="hide-form">
                {str tag='cancel'}
                <span class="icon icon-chevron-up pls"></span>
            </span>
        </button>

        {if $license}
        <div class="resumelicense">
        {$license|safe}
        </div>
        {/if}
    </div>
    {/if}
</div>
