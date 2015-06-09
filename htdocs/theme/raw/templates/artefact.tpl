<!-- <h3 class="title">
    {$title}
</h3> -->

{if $tags}
<p class="tags s">
    <strong>{str tag=tags}:</strong> 
    {list_tags owner=$owner tags=$tags}
</p>
{/if}

<p>{$description|clean_html|safe}</p>

{if isset($attachments)}
<div class="has-attachment panel panel-default collapsible">
    <h5 class="panel-heading">
        <a href="#atrtefact-attach" class="text-left pts pbm collapsed" aria-expanded="false" data-toggle="collapse">
            <span class="fa fa-paperclip prm"></span>

            <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
            <span class="metadata">({$attachments|count})</span>
            <span class="fa pts fa-chevron-down pull-right collapse-indicator"></span>
        </a>
    </h5>
        <!-- Attachment list with view and download link -->
    <div id="atrtefact-attach" class="collapse">
        <ul class="list-unstyled list-group mb0">
            {foreach from=$attachments item=item}
            <li class="list-group-item">
                <a href="{$item->downloadpath}" class="outer-link icon-on-hover">
                    <span class="sr-only">
                        {str tag=Download section=artefact.file} {$item->title}
                    </span>
                </a>

                {if $item->icon}
                <img src="{$item->iconpath}" alt="">
                {else}
                <span class="fa fa-{$item->artefacttype} fa-lg text-default"></span>
                {/if}

                <span class="title list-group-item-heading plm text-inline">
                    <a href="{$item->viewpath}" class="inner-link">
                        {$item->title}
                    </a>
                    <span class="metadata"> - 
                        [{$item->size|display_size}]
                    </span>
                </span>
                <span class="fa fa-download fa-lg pull-right pts text-watermark icon-action"></span>
            </li>
            {/foreach}
        </ul>
    </div>
</div>
{/if}

{if $license}
    <div class="artefactlicense ptl pbl">
        {$license|safe}
    </div>
{/if}