{$description|clean_html|safe}

{if isset($attachments)}
<div class="has-attachment panel panel-default collapsible">
    <h4 class="panel-heading">
        <a class="text-left pts pbm collapsed" aria-expanded="false" href="#cv-attach-{$id}" data-toggle="collapse">
            <span class="fa prm fa-paperclip"></span>

            <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
            <span class="metadata">({$count})</span>
            <span class="fa pts fa-chevron-down pull-right collapse-indicator"></span>
        </a>
    </h4>

    <div id="cv-attach-{$id}" class="collapse">
        <ul class="list-unstyled list-group">
        {foreach from=$attachments item=item}
            <li class="list-group-item-text list-group-item-link">
                <a href="{$item->downloadpath}">
                    {if $item->iconpath}
                    <img src="{$item->iconpath}" alt="">
                    {else}
                    <span class="fa fa-{$item->artefacttype} fa-lg text-default"></span>
                    {/if}
                    {$item->title|truncate:50}
                </a>
            </li>
        {/foreach}
        </ul>
    </div>
</div>

{/if}
