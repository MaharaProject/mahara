{**
* This template displays a blog post.
*}
<div id="blogpost-{$postid}" class="panel-body">
    
    {if $artefacttitle && $simpledisplay}
    <h3 class="title">
        {$artefacttitle|safe}
    </h3>
    {/if}

    <div class="postdetails metadata mbm">
        <span class="fa fa-calendar mrs"></span>
        {$postedbyon}
    </div>

    {if $artefact->get('tags')}
    <div class="tags metadata">
        <span class="fa fa-tags"></span>
        <strong>{str tag=tags}:</strong> 
        {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}
    </div>
    {/if}

    {$artefactdescription|clean_html|safe}

    {if $license}
    <div class="postlicense mtm mbl">
        {$license|safe}
    </div>
    {/if}

    {if isset($attachments)}
        <div class="has-attachment panel panel-default collapsible">
            <h5 class="panel-heading">
                <a class="text-left pts pbm collapsed" aria-expanded="false" href="#blog-attach-{$postid}" data-toggle="collapse">
                    <span class="fa prm fa-paperclip"></span>

                    <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                    <span class="metadata">({$attachments|count})</span>
                    <span class="fa pts fa-chevron-down pull-right collapse-indicator"></span>
                </a>
            </h5>
            <!-- Attachment list with view and download link -->
            <div id="blog-attach-{$postid}" class="collapse">
                <ul class="list-unstyled list-group">
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
                        <span class="fa fa-{$item->artefacttype} fa-lg text-default"></span>
                        {/if}

                        <span class="title list-group-item-heading plm inline">
                            <a href="{$item->viewpath}" class="inner-link">
                                {$item->title}
                            </a>
                            <span class="metadata"> - 
                                [{$item->size|display_size}]
                            </span>
                        </span>

                        <span class="fa fa-download fa-lg pull-right pts text-watermark icon-action"></span>
                        {if $item->description}
                        <div class="description ptm">
                            <p class="text-small">
                                {$item->description}
                            </p>
                        </div>
                        {/if}
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
    {/if}
</div>
