        <div class="has-attachment card collapsible">
            <div class="card-header">
                <a class="text-left collapsed" aria-expanded="false" href="#blog-attach-{$postid}" data-toggle="collapse">
                    <span class="icon left icon-paperclip icon-sm" role="presentation" aria-hidden="true"></span>

                    <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                    <span class="metadata">({$attachments|count})</span>
                    <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                </a>
            </div>
            <!-- Attachment list with view and download link -->
            <div id="blog-attach-{$postid}" class="collapse">
                <ul class="list-unstyled list-group">
                {foreach from=$attachments item=item}
                    <li class="list-group-item">
                    {if $item->iconpath}
                        <img class="file-icon" src="{$item->iconpath}" alt="">
                    {else}
                        <span class="icon icon-{$item->artefacttype} icon-lg text-default left" role="presentation" aria-hidden="true"></span>
                    {/if}
                    {if !$editing}
                    <span class="title">
                        <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-blockid="{$blockid}" data-artefactid="{$item->id}">
                            <span class="text-small">{$item->title}</span>
                        </a>
                    </span>
                    {else}
                        <span class="title">
                            <span class="text-small">{$item->title}</span>
                        </span>
                    {/if}
                        <a href="{$item->downloadpath}">
                            <span class="sr-only">{str tag=Download section=artefact.file} {$item->title}</span>
                            <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true" data-toggle="tooltip" title="{str tag=Download section=artefact.file} {$item->title}"></span>
                        </a>
                        {if $item->description}
                        <div class="file-description text-small">
                            {$item->description|clean_html|safe}
                        </div>
                        {/if}
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
