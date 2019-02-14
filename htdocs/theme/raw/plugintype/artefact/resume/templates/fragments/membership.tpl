{if $controls}
<div class="card">
    {if !$hidetitle}
    <h3 class="resumeh3 card-header">
        {str tag='membership' section='artefact.resume'}
        {if $controls}
            {contextualhelp plugintype='artefact' pluginname='resume' section='addmembership'}
        {/if}
    </h3>{/if}
    <table id="membershiplist{$suffix}" class="tablerenderer resumefive resumecomposite fullwidth table">
        <thead>
            <tr>
                {if $controls}
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=move}</span>
                </th>{/if}

                <th>{str tag='title' section='artefact.resume'}</th>

                <th class="resumeattachments text-center">
                    <span>{str tag=Attachments section=artefact.resume}</span>
                </th>

                {if $controls}
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=edit}</span>
                </th>
                {/if}
            </tr>
        </thead>
        <!-- Table body is rendered by javascript on content-> resume -->
    </table>

    {if $controls}
    <div class="card-footer has-form">
        <div id="membershipform" class="collapse" data-action='focus-on-open reset-on-collapse'>
            {$compositeforms.membership|safe}
        </div>
        <button id="addmembershipbutton" data-toggle="collapse" data-target="#membershipform" aria-expanded="false" aria-controls="membershipform"class="float-right btn btn-secondary btn-sm collapsed expand-add-button">
            <span class="show-form">
                {str tag='add'}
                <span class="icon icon-chevron-down right" role="presentation" aria-hidden="true"></span>
                <span class="accessible-hidden sr-only">{str tag='addmembership' section='artefact.resume'}</span>
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
    {/if}
</div>
{/if}

<!-- Render membership blockinstance on page view -->
<div id="membershiplist{$suffix}" class="list-group list-group-lite">
    {foreach from=$rows item=row}
    <div class="list-group-item">
        <h5 class="list-group-item-heading">
        {if $row->description || $row->attachments}
            <a href="#membership-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="text-left collapsed collapsible" aria-expanded="false" data-toggle="collapse">
                {$row->title}
                <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                <br />
                <span class="text-small text-muted">
                    {$row->startdate}{if $row->enddate} - {$row->enddate}{/if}
                </span>
            </a>
        {else}
            {$row->title}
            <br />
            <span class="text-small text-muted">
                {$row->startdate}{if $row->enddate} - {$row->enddate}{/if}
            </span>
        {/if}
        </h5>

        <div id="membership-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="collapse resume-content">
            {if $row->description}
            <p class="content-text">
                {$row->description|safe}
            </p>
            {/if}

            {if $row->attachments}
            <h5 class="list-group-item-heading">
                <span class="icon icon-paperclip left" role="presentation" aria-hidden="true"></span>
                <span>{str tag='attachedfiles' section='artefact.blog'}</span>
                ({$row->clipcount})
            </h5>
            <ul class="list-group list-group-unbordered">
                {foreach from=$row->attachments item=item}
                <li class="list-group-item">
                    <a href="{$item->downloadpath}" class="outer-link icon-on-hover">
                        <span class="sr-only">{str tag=Download section=artefact.file} {$item->title}</span>
                    </a>

                    {if $item->iconpath}
                    <img class="file-icon" src="{$item->iconpath}" alt="">
                    {else}
                    <span class="icon icon-{$item->artefacttype} left icon-lg text-default" role="presentation" aria-hidden="true"></span>
                    {/if}

                    <span class="title text-inline">
                        <a href="{$item->viewpath}" class="text-small inner-link">
                            {$item->title}
                        </a>
                        <span class="metadata"> -
                            [{$item->size}]
                        </span>
                    </span>

                    <span class="icon icon-download icon-lg float-right text-watermark icon-action inner-link" role="presentation" aria-hidden="true"></span>
                </li>
                {/foreach}
            </ul>
            {/if}
        </div>
    </div>
    {/foreach}
</div>
