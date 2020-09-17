{if $controls}
<div class="card">
    {if !$hidetitle}
    <h2 class="resumeh3 card-header">
        {str tag='educationhistory' section='artefact.resume'}
        {contextualhelp plugintype='artefact' pluginname='resume' section='addeducationhistory'}
    </h2>
    {/if}

    <table id="educationhistorylist{$suffix}" class="resumefive resumecomposite fullwidth table">
        <thead>
            <tr>
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=move}</span>
                </th>
                <th>{str tag='qualification' section='artefact.resume'}</th>
                <th class="resumeattachments">
                    <span class="">{str tag=Attachments section=artefact.resume}</span>
                </th>
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=edit}</span>
                </th>
            </tr>
        </thead>
    <!-- Table body is rendered by javascript on content-> resume -->
    </table>

    <div class="card-footer has-form">
        <div id="educationhistoryform" class="collapse" data-action='focus-on-open reset-on-collapse'>
            {$compositeforms.educationhistory|safe}
        </div>

        <button id="addeducationhistorybutton" data-toggle="collapse" data-target="#educationhistoryform" aria-expanded="false" aria-controls="educationhistoryform" class="float-right btn btn-secondary btn-sm collapsed expand-add-button">
            <span class="show-form">
                {str tag='add'}
                <span class="icon icon-chevron-down right" role="presentation" aria-hidden="true"></span>
                <span class="accessible-hidden sr-only">{str tag='addeducationhistory' section='artefact.resume'}</span>
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
{else}
<!-- Render education blockinstance on page view -->
<div id="educationhistorylist{$suffix}" class="list-group list-group-lite list-group-top-border">
    {foreach from=$rows item=row}
    <div class="list-group-item flush-collapsible">
        {if $row->qualdescription || $row->attachments || $row->institutionaddress}
            <h3 class="list-group-item-heading">
                <a href="#education-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="text-left collapsed collapsible" aria-expanded="false" data-toggle="collapse">
                    {$row->qualification}
                    <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                </a>
            </h3>
        {else}
            <h3 class="list-group-item-heading">
                {$row->qualification}
            </h3>
        {/if}

        <span class="text-small text-muted">
            {$row->startdate}
            {if $row->enddate} - {$row->enddate}{/if}
        </span>

        <div id="education-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="collapse resume-content">
            {if $row->qualdescription}
            <p class="content-text">
                {$row->qualdescription|safe}
            </p>
            {/if}

            {if $row->institutionaddress}
                <span class="text-small text-muted">
                    {str tag=addresstag section='blocktype.resume/entireresume' arg1=$row->institutionaddress}
                </span>
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
{/if}
