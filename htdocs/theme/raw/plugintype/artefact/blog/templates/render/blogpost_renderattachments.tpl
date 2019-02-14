        <div class="has-attachment card collapsible">
            <h3 class="card-header">
                <a class="text-left collapsed" aria-expanded="false" href="#blog-attach-{$postid}" data-toggle="collapse">
                    <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>

                    <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                    <span class="metadata">({$attachments|count})</span>
                    <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                </a>
            </h3>
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

                        <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
