{include file="header.tpl"}

    <div class="row manage-collection-pages" id="collectionpages" data-collectionid="{$id}">
        <div class="col-md-12">
            <p class="lead">{str tag=collectiondragupdate1 section=collection}</p>
            <fieldset class="card card-half first pagelist draggable " id="pagestoadd">
                <h2 class="card-header">
                    {str tag=addviewstocollection section=collection}
                    {if $viewsform}
                        <span class="btn-group select-pages" role="group">
                            <a class="btn btn-sm btn-secondary" href="" id="selectall">{str tag=All}</a>
                            <a class="btn btn-sm btn-secondary" href="" id="selectnone">{str tag=none}</a>
                        </span>
                    {/if}
                </h2>
                <div class="pagesavailable">
                    {if $viewsform}
                    {$viewsform|safe}
                    {/if}
                    <div id="nopagetoadd" class="no-results lead text-small {if $viewsform} d-none{/if}">
                        {str tag=noviewsavailable section=collection}
                    </div>
                </div>
            </fieldset>
            <fieldset class="card card-half collection-pages droppable" id="pagesadded">
                <h2 class="card-header">{str tag=viewsincollection section=collection}</h2>
                {if !$views}
                    <div class="message dropzone-previews full-width">
                        <div class="dz-message">
                            {str tag=noviews section=collection}
                        </div>
                    </div>
                {else}
                <ol class="list-group" id="collectionviews">
                    {foreach from=$views.views item=view}
                        <li class="list-group-item" id="row_{$view->view}">
                            {if $views.count > 1}
                                {if $view->displayorder == $views.min}
                                    <a class="btn btn-sm text-default order-sort-control single-arrow-down text-midtone" href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                                        <span class="icon icon-long-arrow-alt-down" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag=moveitemdown}</span>
                                    </a>
                                {elseif $view->displayorder == $views.max}
                                    <a class="btn btn-sm text-default order-sort-control single-arrow-up text-midtone" href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                                        <span class="icon icon-long-arrow-alt-up left" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag=moveitemup}</span>
                                    </a>
                                {else}
                                    <a class="btn btn-sm text-default order-sort-control" href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                                        <span class="icon icon-long-arrow-alt-up left text-midtone" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag=moveitemup}</span>
                                    </a>
                                    <a class="btn btn-sm text-default order-sort-control" href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                                        <span class="icon icon-long-arrow-alt-down text-midtone" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag=moveitemdown}</span>
                                    </a>
                                {/if}
                            {/if}
                            <a href="{$view->fullurl}" class="text-link">
                                {$view->title}
                            </a>
                            {$view->remove|safe}
                        </li>

                    {/foreach}
                    </ol>

                {/if}
            </fieldset>
        </div>
    </div>

    <div id="collectiondonewrap" class=" primary submitcancel form-group">
        <a class="btn btn-primary submitcancel submit" href="{$accessurl}">{str tag=nexteditaccess section=collection}</a>
        <a class="btn submitcancel cancel" href="{$baseurl}">{str tag=cancel}</a>
    </div>

{include file="footer.tpl"}
