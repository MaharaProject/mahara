{**
* This template displays a blog post.
*}
<div id="blogpost-{$postid}" class="panel-body">
    {if $artefacttitle}<h3 class="title">{$artefacttitle|safe}</h3>{/if}

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
            <h4 class="panel-heading">
                <a class="text-left pts pbm collapsed" aria-expanded="false" href="#blog-attach-{$postid}" data-toggle="collapse">
                    <span class="fa prm fa-paperclip"></span>

                    <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                    <span class="metadata">({$attachments|count})</span>
                    <span class="fa pts fa-chevron-down pull-right collapse-indicator"></span>
                </a>
            </h4>


            <div id="blog-attach-{$postid}" class="collapse">
                <ul class="list-unstyled list-group">
                {foreach from=$attachments item=item}
                    <li class="list-group-item-text list-group-item-link">
                        <a href="{$item->downloadpath}">
                            <div class="file-icon mrs">
                                {if $item->iconpath}
                                <img src="{$item->iconpath}" alt="">
                                {else}
                                <span class="fa fa-{$item->artefacttype} fa-lg text-default"></span>
                                {/if}
                            </div>
                            {$item->title|truncate:25}
                        </a>
                    </li>
                {/foreach}
                </ul>
            </div>
        </div>
    {/if}
</div>
