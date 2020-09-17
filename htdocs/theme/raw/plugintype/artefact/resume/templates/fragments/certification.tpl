{if $controls}
<div class="card">
    {if !$hidetitle}
    <h2 class="resumeh3 card-header">
        {str tag='certification' section='artefact.resume'}
        {contextualhelp plugintype='artefact' pluginname='resume' section='addcertification'}
    </h2>
    {/if}

    <table id="certificationlist{$suffix}" class="tablerenderer resumefour resumecomposite fullwidth table">
        <thead>
            <tr>
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=move}</span>
                </th>

                <th>{str tag='title' section='artefact.resume'}</th>

                <th class="resumeattachments">
                    <span>{str tag=Attachments section=artefact.resume}</span>
                </th>

                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=edit}</span>
                </th>
            </tr>
        </thead>
    <!-- Table body is rendered by javascript on content-> resume -->
    </table>

    <div class="card-footer has-form">
        <div id="certificationform" class="collapse" data-action='focus-on-open reset-on-collapse'>
            {$compositeforms.certification|safe}
        </div>

        <button id="addcertificationbutton" data-toggle="collapse" data-target="#certificationform" aria-expanded="false" aria-controls="certificationform" class="float-right btn btn-secondary btn-sm collapsed expand-add-button">
            <span class="show-form">
                {str tag='add'}
                <span class="icon icon-chevron-down right" role="presentation" aria-hidden="true"></span>
                <span class="accessible-hidden sr-only">{str tag='addcertification' section='artefact.resume'}</span>
            </span>
            <span class="hide-form">
                {str tag='cancel'}
                <span class="icon icon-chevron-up right" role="presentation" aria-hidden="true"></span>
            </span>
        </button>

        {if $license}
        <div class="license">
        {$license|safe}
        </div>
        {/if}
    </div>
</div>
{/if}

<!-- Render certification blockinstance on page view -->
<div id="certificationlist{$suffix}" class="list-group list-group-lite list-group-top-border">
    {foreach from=$rows item=row}
    <div class="list-group-item flush-collapsible">

        {if $row->description || $row->attachments}
            <h4 class="list-group-item-heading">
                <a href="#certification-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="text-left collapsed collapsible" aria-expanded="false" data-toggle="collapse">
                    {$row->title}
                    <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                </a>
            </h4>
        {else}
            <h4 class="list-group-item-heading">
                {$row->title}
            </h4>
        {/if}
        <span class="text-small text-muted">
            {$row->date}
        </span>

        <div id="certification-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="collapse resume-content">
            {if $row->description}
            <p class="content-text">
                {$row->description|safe}
            </p>
            {/if}

            {if $row->attachments}
            <div class="has-attachment card">
                <div class="card-header">
                    <span class="icon icon-paperclip left icon-sm" role="presentation" aria-hidden="true"></span>
                    <span class="text-small">{str tag='attachedfiles' section='artefact.blog'}</span>
                    <span class="metadata">({$row->clipcount})</span>
                </div>
                <ul class="list-unstyled list-group">
                    {foreach from=$row->attachments item=item}
                    {if !$item->allowcomments}
                        {assign var="justdetails" value=true}
                    {/if}
                    {include
                        file='header/block-comments-details-header.tpl'
                        artefactid=$item->id
                        blockid=$blockid
                        commentcount=$item->commentcount
                        allowcomments=$item->allowcomments
                        justdetails=$justdetails
                        displayiconsonly = true}
                    <li class="list-group-item">
                    {if !$editing}
                        <a class="modal_link file-icon-link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-artefactid="{$item->id}">
                        {if $item->iconpath}
                            <img class="file-icon" src="{$item->iconpath}" alt="">
                        {else}
                            <span class="icon icon-{$item->artefacttype} left icon-lg text-default file-icon" role="presentation" aria-hidden="true"></span>
                        {/if}
                        </a>
                    {else}
                        <span class="file-icon-link">
                        {if $item->iconpath}
                            <img class="file-icon" src="{$item->iconpath}" alt="">
                        {else}
                            <span class="icon icon-{$item->artefacttype} left icon-lg text-default file-icon" role="presentation" aria-hidden="true"></span>
                        {/if}
                        </span>
                    {/if}
                        <span class="title">
                        {if !$editing}
                            <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-artefactid="{$item->id}">
                        {/if}
                            <span class="text-small">{$item->title}</span>
                        {if !$editing}
                            </a>
                        {/if}
                        </span>
                        <a href="{$item->downloadpath}" class="download-link">
                            <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true" data-toggle="tooltip" title="{str tag=downloadfilesize section=artefact.file arg1=$item->title arg2=$item->size}"></span>
                        </a>
                    {if $item->description}
                        <div class="file-description text-small text-midtone">
                            {$item->description|clean_html|safe}
                        </div>
                    {/if}
                    </li>
                    {/foreach}
                </ul>
            </div>
            {/if}
        </div>
    </div>
    {/foreach}
</div>
