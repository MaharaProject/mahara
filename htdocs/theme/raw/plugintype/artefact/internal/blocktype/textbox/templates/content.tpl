<div class="ptl">
    {$text|clean_html|safe}

    {if $artefact->get('tags')}
    <div class="tags">
        {str tag=tags}: {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}
    </div>
    {/if}
</div>

{if $attachments}
{* @TODO: this could probably be a reusable template *}
<div class="has-attachment panel panel-default collapsible">
    <h4 class="panel-heading">
        <a class="text-left pts pbm collapsed" aria-expanded="false" href="#note-attach-{$blockid}" data-toggle="collapse">
            <span class="icon prm icon-paperclip"></span>

            <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
            <span class="metadata">({$count})</span>
            <span class="icon pts icon-chevron-down pull-right collapse-indicator"></span>
        </a>
    </h4>
    {* Attachment list with view and download link *}
    <div id="note-attach-{$blockid}" class="collapse">
        <ul class="list-unstyled list-group mb0">
            {foreach from=$attachments item=item}
            <li class="list-group-item">
                <a href="{$item->downloadpath}" class="outer-link icon-on-hover">
                    <span class="sr-only">
                        {str tag=Download section=artefact.file} {$item->title}
                    </span>
                </a>
                {if $item->iconpath}
                <img src="{$item->iconpath}" alt="">
                {else}
                <span class="icon icon-{$item->artefacttype} icon-lg text-default"></span>
                {/if}

                <span class="title list-group-item-heading plm text-inline">
                    <a href="{$item->viewpath}" class="inner-link">
                        {$item->title}
                    </a>
                    <span class="metadata"> -
                        [{$item->size|display_size}]
                    </span>
                </span>
                <span class="icon icon-download icon-lg pull-right pts text-watermark icon-action"></span>
            </li>
            {/foreach}
        </ul>
    </div>
</div>
{/if}

{$comments|safe}
