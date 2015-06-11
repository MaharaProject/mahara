{if $controls}
<div class="panel panel-default">
    {if !$hidetitle}
    <h3 class="resumeh3 panel-heading">
        {str tag='book' section='artefact.resume'}
        {contextualhelp plugintype='artefact' pluginname='resume' section='addbook'}
    </h3>{/if}

    <table id="booklist{$suffix}" class="tablerenderer resumefour resumecomposite fullwidth table">
        <thead>
            <tr>
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=move}</span>
                </th>
                <th>{str tag='title' section='artefact.resume'}</th>
                <th class="resumeattachments text-center">
                    <span>{str tag=Attachments section=artefact.resume}</span>
                </th>
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=edit}</span>
                </th>
            </tr>
        </thead>
        <!-- Table body is rendered by javascript on content-> resume -->
    </table>

    <div class="panel-footer has-form">
        <div id="bookform" class="js-expanded-form collapse mtl mlm" data-action='reset-on-collapse'>
            {$compositeforms.book|safe}
        </div>
        <button id="addbookbutton" data-toggle="collapse" data-target="#bookform" aria-expanded="false" aria-controls="bookform" class="pull-right btn btn-default btn-sm collapsed expand-add-button">
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
</div>
{/if}

<!-- Render book blockinstance on page view -->
<div id="booklist{$suffix}" class="list-group list-group-lite">
    {foreach from=$rows item=row}
    <div class="list-group-item">
        <h5 class="mt0 list-group-item-heading">
        {if $row->description || $row->attachments || $row->url}
            <a href="#book-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="text-left collapsed collapsible" aria-expanded="false" data-toggle="collapse">
                {$row->title}
                <span class="icon pts icon-chevron-down pull-right collapse-indicator"></span>
                <br />
                {if $row->date}
                <span class="text-small text-muted">
                    {$row->date}
                </span>
                {/if}
            </a>
        {else}
            {$row->title}
            <br />
            {if $row->date}
            <span class="text-small text-muted">
                {$row->date}
            </span>
            {/if}
        {/if}
        </h5>

        <div id="book-content-{$row->id}{if $artefactid}-{$artefactid}{/if}" class="collapse resume-content mtm">
            {if $row->description}
            <p class="compositedesc">
                {$row->description}
            </p>
            {/if}
            
            {if $row->url}
            <p>
                <a href="{$row->url}" target="_blank">{$row->url}</a>
            </p>
            {/if}
            
            {if $row->attachments}
            <h5 class="plm list-group-item-heading">
                <span class="icon icon-paperclip prs"></span>
                <span>{str tag='attachedfiles' section='artefact.blog'}</span>
                ({$row->clipcount})
            </h5>
            <ul class="list-group mb0">
                {foreach from=$row->attachments item=item}
                <li class="list-group-item">
                    <a href="{$item->downloadpath}" class="outer-link icon-on-hover">
                        <span class="sr-only">{str tag=Download section=artefact.file} {$item->title}</span>
                    </a> 
                    
                    {if $item->iconpath}
                    <img src="{$item->iconpath}" alt="">
                    {else}
                    <span class="icon icon-{$item->artefacttype} icon-lg text-default"></span>
                    {/if}

                    <span class="title plm text-inline">
                        <a href="{$item->viewpath}" class="inner-link">
                            {$item->title}
                        </a>
                        <span class="metadata"> -
                            [{$item->size}]
                        </span>
                    </span>

                    <span class="icon icon-download icon-lg pull-right pts text-watermark icon-action inner-link"></span>
                </li>
                {/foreach}
            </ul>
            {/if}
        </div>
    </div>
    {/foreach}
</div>
