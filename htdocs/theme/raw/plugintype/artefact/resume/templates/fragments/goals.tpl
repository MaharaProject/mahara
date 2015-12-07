<div class="goals-wrapper">
    {if !$hidetitle}
    <h3 class="resumeh3">
        {str tag='mygoals' section='artefact.resume'}
        {if $controls}
        {contextualhelp plugintype='artefact' pluginname='resume' section='mygoals'}
        {/if}
    </h3>{/if}
    <div id="goalslist{$suffix}" class="panel-items panel-items-no-margin js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
        {foreach from=$goals item=n, name='default'}
        <div class="panel panel-default">
            <h4 class="panel-heading has-link">
                {if $n->exists}
                    <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?id={$n->id}" title="{str tag=edit$n->artefacttype section=artefact.resume}">
                    {str tag=$n->artefacttype section='artefact.resume'}
                    <span class="icon icon-pencil pull-right" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=edit}</span>
                    </a>
                {else}
                    <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?type={$n->artefacttype}" title="{str tag=edit$n->artefacttype section=artefact.resume}">
                          {str tag=$n->artefacttype section='artefact.resume'}
                    <span class="icon icon-pencil pull-right" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=edit}</span>
                    </a>
                {/if}
            </h4>
            <div class="panel-body">
                {if $n->description != ''}
                {$n->description|clean_html|safe}
                {else}
                <p class="no-results-small">
                    {str tag=nodescription section=artefact.resume}
                </p>
                {/if}
            </div>
            {if $n->files}
            <div id="resume_{$n->id}" class="has-attachment">
                <a class="collapsible collapsed panel-footer" aria-expanded="false" href="#attach_goal_{$.foreach.default.index}" data-toggle="collapse">
                    <p class="text-left">
                        <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>

                        <span class="text-small">{str tag=attachedfiles section=artefact.blog}</span>
                         <span class="metadata">({$n->count})</span>
                        <span class="icon icon-chevron-down pull-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                    </p>
                </a>


                <div id="attach_goal_{$.foreach.default.index}" class="collapse">
                    <ul class="list-unstyled list-group">
                    {foreach from=$n->files item=file}
                        <li class="list-group-item-text list-group-item-link">
                            <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}" '{if $file->description}' title="{$file->description}" data-toggle="tooltip" '{/if}' >
                                {if $file->icon}
                                <img src="{$file->icon}" alt="" class="file-icon">
                                {else}
                                <span class="icon icon-{$file->artefacttype} icon-lg text-default" role="presentation" aria-hidden="true"></span>
                                {/if}
                                {$file->title|truncate:40}
                            </a>
                        </li>
                    {/foreach}
                    </ul>
                </div>
            </div>
            {/if}
            </div>
        {/foreach}
    </div>
    {if $license}
    <div class="license">
        {$license|safe}
    </div>
    {/if}
</div>
