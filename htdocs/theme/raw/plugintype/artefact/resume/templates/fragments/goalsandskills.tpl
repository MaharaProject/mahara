<div class="panel-body flush">
{$description|clean_html|safe}

{if isset($attachments)}
<div class="has-attachment panel panel-default collapsible">
    <h4 class="panel-heading">
        <a class="text-left collapsed" aria-expanded="false" href="#cv-attach-{$id}{if $artefactid}-{$artefactid}{/if}" data-toggle="collapse">
            <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>

            <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
            <span class="metadata">({$count})</span>
            <span class="icon icon-chevron-down pull-right collapse-indicator" role="presentation" aria-hidden="true"></span>
        </a>
    </h4>

    <!-- Attachment list with view and download link -->
    <div id="cv-attach-{$id}{if $artefactid}-{$artefactid}{/if}" class="collapse">
        <ul class="list-unstyled list-group">
        {foreach from=$attachments item=item}
            <li class="list-group-item">
                <a href="{$item->downloadpath}" class="outer-link icon-on-hover">
                    <span class="sr-only">
                        {str tag=Download section=artefact.file} {$item->title}
                    </span>
                </a>

                {if $item->iconpath}
                <img class="file-icon" src="{$item->iconpath}" alt="">
                {else}
                <span class="icon icon-{$item->artefacttype} icon-lg text-default" role="presentation" aria-hidden="true"></span>
                {/if}

                <span class="title list-group-item-heading">
                    <a href="{$item->viewpath}" class="inner-link">
                        {$item->title}
                    </a>
                    <span class="metadata"> -
                        [{$item->size|display_size}]
                    </span>
                </span>

                <span class="icon icon-download icon-lg pull-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
            </li>
        {/foreach}
        </ul>
    </div>
</div>
{/if}
</div>
