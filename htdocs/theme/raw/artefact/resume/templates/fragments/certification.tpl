{if $controls}
<div class="panel panel-default">
    {if !$hidetitle}
    <h3 class="resumeh3 panel-heading">
        {str tag='certification' section='artefact.resume'}
        {if $controls}
        {contextualhelp plugintype='artefact' pluginname='resume' section='addcertification'}
        {/if}
    </h3>
    {/if}

    <table id="certificationlist{$suffix}" class="tablerenderer resumefour resumecomposite fullwidth table">
        <thead>
            <tr>
                {if $controls}
                <th class="resumecontrols">
                    <span class="accessible-hidden sr-only">{str tag=move}</span>
                </th>
                {/if}
                
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
{/if}

<!-- Render certificationt blockinstance on page view -->
<div class="list-group list-group-lite">
    {foreach from=$rows item=row}
    <div class="list-group-item">
        <h4 class="mt0 list-group-item-heading">
        {if $row->description || $row->attachments}
            <a href="#certification-content-{$row->id}-{$id}" class="text-left collapsed collapsible" aria-expanded="false" data-toggle="collapse">
                {$row->title}
                <span class="icon pts icon-chevron-down pull-right collapse-indicator"></span>
                <br />
                <span class="text-small text-muted">
                    {$row->date}
                </span>
            </a>
        {else}
            {$row->title}
            <br />
            <span class="text-small text-muted">
                {$row->date}
            </span>
        {/if}
        </h4>

        <div id="certification-content-{$row->id}-{$id}" class="collapse resume-content mtm">
            {if $row->description}
            <p class="compositedesc">
                {$row->description}
            </p>
            {/if}
            
            {if $row->attachments}
            <h5 class="plm">
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