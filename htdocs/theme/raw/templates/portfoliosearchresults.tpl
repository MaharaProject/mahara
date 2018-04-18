{foreach from=$data item=result}
    <div class="list-group-item">
        <div class="row">
        {if $result->type == 'artefact'}
            <div class="col-md-8">
            {if $result->thumb}
                <img src="{$result->thumb}" alt="" class="artefact-img">
                <h3 class="title list-group-item-heading text-inline">
                {if $result->link}
                    <a href="{$WWWROOT}{$result->link}">
                        {$result->title|str_shorten_html:50:true|safe}
                    </a>
                {else}
                    {$result->title|str_shorten_html:50:true|safe}
                {/if}
                </h3>
            {else}
                <h3 class="title list-group-item-heading text-inline">
                <span class="icon left float-left icon-{$result->typestr}" role="presentation" aria-hidden="true"></span>
                {if $result->link}
                    <a href="{$WWWROOT}{$result->link}">
                        {$result->title|str_shorten_html:50:true|safe}
                    </a>
                {else}
                    {$result->title|str_shorten_html:50:true|safe}
                {/if}
                </h3>
            {/if}

                <span class="artefacttype text-midtone">
                {if $result->artefacttype == "blogpost"}
                    ({str tag=blogpost section=search.elasticsearch})
                {elseif $result->artefacttype == "forumpost"}
                    ({str tag=forumpost section=search.elasticsearch})
                {elseif $result->artefacttype == "resume"}
                    ({str tag=resume section=search.elasticsearch})
                {elseif $result->artefacttype == "wallpost"}
                    ({str tag=wallpost section=search.elasticsearch})
                {else}
                    ({$result->typelabel})
                {/if}
                </span>
                <div class="detail">
                {$result->description|str_shorten_html:140:true|safe}
                </div>
                <!-- TAGS -->
                {if is_array($result->tags) && count($result->tags) > 0}
                <div class="tags">
                    <strong>{str tag=tags}: </strong>
                    {list_tags tags=$result->tags owner=$owner view=$result->viewid}
                </div>
                {/if}
            </div>
            <div class="col-md-4">
            <!-- VIEWS -->
            {if is_array($result->views) && count($result->views) > 0}
                <div class="usedon">
                {if count($result->views) > 1}
                    <strong>{str tag=usedonpages section=search.elasticsearch}:</strong>
                {else}
                    <strong>{str tag=usedonpage section=search.elasticsearch}:</strong>
                {/if}
                    <ul class="list-group list-unstyled">
                    {foreach from=$result->views key=id item=view}
                    <li>
                        <a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>
                        <!-- Profile artefact can only be displayed in views -->
                        {if $view->type != "profile"} |
                            <span class="viewartefact">
                                <a href="{$WWWROOT}artefact/artefact.php?artefact={$result->id}&view={$id}">
                                    {str tag=viewartefact}
                                    {if $result->artefacttype == "blogpost"}
                                        {str tag=blogpost section=search.elasticsearch}
                                    {elseif $result->artefacttype == "forumpost"}
                                        {str tag=forumpost section=search.elasticsearch}
                                    {elseif $result->artefacttype == "resume"}
                                        {str tag=resume section=search.elasticsearch}
                                    {elseif $result->artefacttype == "wallpost"}
                                        {str tag=wallpost section=search.elasticsearch}
                                    {elseif $result->artefacttype == "blog"}
                                        {str tag=blog section=search.elasticsearch}
                                    {elseif $result->artefacttype == "html"}
                                        {str tag=html section=search.elasticsearch}
                                    {else}
                                        {$result->artefacttype|lower}
                                    {/if}
                                </a>
                            </span>
                        {/if}
                    </li>
                    {/foreach}
                    </ul>
                </div>
            {/if}
            </div>
        {elseif $result->type == 'blocktype'}
            <div class="col-md-8">
                <h3 class="title list-group-item-heading text-inline">
                <span class="icon left pull-left icon-{$result->typestr}" role="presentation" aria-hidden="true"></span>
                {if $result->link}
                    <a href="{$WWWROOT}{$result->link}">
                        {$result->title|str_shorten_html:50:true|safe}
                    </a>
                {else}
                    {$result->title|str_shorten_html:50:true|safe}
                {/if}
                </h3>

                <span class="artefacttype text-midtone">
                    ({$result->typelabel})
                </span>
                <div class="detail">
                {$result->description|str_shorten_html:140:true|safe}
                </div>
                <!-- TAGS -->
                {if is_array($result->tags) && count($result->tags) > 0}
                <div class="tags">
                    <strong>{str tag=tags}: </strong>
                    {list_tags tags=$result->tags owner=$owner view=$result->viewid}
                </div>
                {/if}
            </div>
            <div class="col-md-4">
            <!-- VIEWS -->
            {if is_array($result->views) && count($result->views) > 0}
                <div class="usedon">
                    <strong>{str tag=usedonpage section=search.elasticsearch}:</strong>
                    <ul class="list-group list-unstyled">
                    {foreach from=$result->views key=id item=view}
                    <li>
                        <a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>
                    </li>
                    {/foreach}
                    </ul>
                </div>
            {/if}
            </div>
        {else}
            <div class="col-md-8">
                <h3 class="list-group-item-heading title text-inline">
                    <span class="icon left float-left icon-{$result->typestr}" role="presentation" aria-hidden="true"></span>
                    <a href="{$result->url}">{$result->title}</a>
                </h3>
                <span class="tag-type text-midtone">({$result->typelabel})</span>
                <p>{$result->ctime}</p>
                <p>
                    {$result->description|str_shorten_html:100|strip_tags|safe}
                </p>
                {if is_array($result->tags) && count($result->tags) > 0}
                <div class="tags"><strong>{str tag=tags}:</strong>
                    {list_tags tags=$result->tags owner=$owner view=$result->viewid}
                </div>
                {/if}
            </div>
            <div class="col-md-4">
                {if is_array($result->views) && count($result->views) > 0}
                    <div class="usedon">
                    {if count($result->views) > 1}
                        <strong>{str tag=views}:</strong>
                    {else}
                        <strong>{str tag=view}:</strong>
                    {/if}
                    {foreach from=$result->views key=id item=view name=views}
                        <a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>{if !$.foreach.views.last}, {/if}
                    {/foreach}
                    </div>
                {/if}
                {if $result->viewtags}
                    <div class="tags">
                        <strong>{str tag=viewtags}: </strong>
                        {list_tags tags=$result->viewtags owner=$owner view=$result->viewid}
                    </div>
                {/if}
            </div>
        {/if}
        </div>
    </div>
{/foreach}
