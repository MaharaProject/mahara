{**
* This template displays a blog post.
*}

<div id="blogpost-{$postid}" class="panel-body flush">

    {if $artefacttitle && $simpledisplay}
    <h3 class="title">
        {$artefacttitle|safe}
    </h3>
    {/if}

    <div class="postdetails metadata">
        <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
        {$postedbyon}
    </div>

    {if $artefact->get('tags')}
    <div class="tags metadata">
        <span class="icon icon-tags" role="presentation" aria-hidden="true"></span>
        <strong>{str tag=tags}:</strong>
        {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}
    </div>
    {/if}

    <div class="postcontent">
    {$artefactdescription|clean_html|safe}
    </div>

    {if $license}
    <div class="license">
        {$license|safe}
    </div>
    {/if}

    {if isset($attachments)}
        <div class="has-attachment panel panel-default collapsible">
            <h5 class="panel-heading">
                <a class="text-left collapsed" aria-expanded="false" href="#blog-attach-{$postid}" data-toggle="collapse">
                    <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>

                    <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                    <span class="metadata">({$attachments|count})</span>
                    <span class="icon icon-chevron-down pull-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                </a>
            </h5>
            <!-- Attachment list with view and download link -->
            <div id="blog-attach-{$postid}" class="collapse">
                <ul class="list-unstyled list-group">
                {foreach from=$attachments item=item}
                    <li class="list-group-item">
                        <a href="{$item->downloadpath}" class="outer-link icon-on-hover" {if $item->description} title="{$item->description}" data-toggle="tooltip"{/if}>
                            <span class="sr-only">
                                {str tag=Download section=artefact.file} {$item->title}
                            </span>
                        </a>

                        {if $item->iconpath}
                        <img class="file-icon" src="{$item->iconpath}" alt="">
                        {else}
                        <span class="icon icon-{$item->artefacttype} icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                        {/if}

                        <span class="title list-group-item-heading inline">
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
