<div class="panel-body">
    {$text|clean_html|safe}

    {if $artefact->get('tags')}
    <div class="tags">
        {str tag=tags}: {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}
    </div>
    {/if}
</div>

{if isset($attachments)}
<!-- to do: this could probably be a reusable template -->
<div class="has-attachment in-panel panel panel-default collapsible last">
    <h4 class="panel-heading">
        <a class="text-left pts pbm collapsed" aria-expanded="false" href="#note-attach-{$blockid}" data-toggle="collapse">
            <span class="fa prm fa-paperclip"></span>

            <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
            <span class="metadata">({$count})</span>
            <span class="fa pts fa-chevron-down pull-right collapse-indicator"></span>
        </a>
    </h4>

    <div id="note-attach-{$blockid}" class="collapse">
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

<div class="mtm">
    {if $commentcount || $commentcount === 0}
    {$comments|safe}
    {/if}
</div>
