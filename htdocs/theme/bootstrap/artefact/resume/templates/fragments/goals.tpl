<div class="goals-wrapper">
    {if !$hidetitle}
    <h3 class="resumeh3">
        {str tag='mygoals' section='artefact.resume'}
        {if $controls}
        {contextualhelp plugintype='artefact' pluginname='resume' section='mygoals'}
        {/if}
    </h3>{/if}
    <div id="goalslist{$suffix}" class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
        {foreach from=$goals item=n}
        <div class="panel panel-default">
            <h3 class="title panel-heading">
                {str tag=$n->artefacttype section='artefact.resume'}
                <div class="pull-right">
                    {if $n->exists}
                    <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?id={$n->id}" title="{str tag=edit$n->artefacttype section=artefact.resume}" class="btn btn-default btn-xs">
                        <span class="fa fa-pencil"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                    {else}
                    <a id="goals_edit_{$n->artefacttype}" href="{$WWWROOT}artefact/resume/editgoalsandskills.php?type={$n->artefacttype}" title="{str tag=edit$n->artefacttype section=artefact.resume}" class="btn btn-default btn-xs">
                        <span class="fa fa-pencil"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                    {/if}
                </div>
            </h3>
            <div id="n{$n->id}_desc" class="panel-body">
                {if $n->description != ''}
                {$n->description|clean_html|safe}
                {else}
                {str tag=nodescription section=artefact.resume}
                {/if}
            </div>
            {if $n->files}
            <div id="resume_{$n->id}" class="panel-footer has-attachment">
                <div class="attachment-heading in-panel">
                    <a class="collapsible collapsed" aria-expanded="false" href="#attach_goal_{$n->id}" data-toggle="collapse">
                        <span class="badge">{$n->count}</span>
                        {str tag=attachedfiles section=artefact.blog}
                        <span class="fa fa-chevron-down pull-right"></span>
                    </a>
                </div>
                <div id="attach_goal_{$n->id}" class="collapse">
                    <ul class="list-group-item-text list-unstyled list-group-item-link has-icon">
                    {foreach from=$n->files item=file}
                        <li>
                            <a href="{$WWWROOT}artefact/file/download.php?file={$file->attachment}" '{if $file->description}' title="{$file->description}" data-toggle="tooltip" '{/if}' >  
                                <div class="file-icon mrs">
                                    <img src="{$file->icon}" alt="">
                                </div>
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
        <div class="resumelicense">
            {$license|safe}
        </div>
        {/if}
</div>
