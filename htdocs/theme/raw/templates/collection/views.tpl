{include file="header.tpl"}

    {if $GROUP}
        <h2>{$PAGESUBHEADING}{if $SUBPAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}</h2>
    {/if}

    <div class="row" id="collectionpages">
        <div class="col-md-12">

            <p class="lead">{str tag=collectiondragupdate1 section=collection}</p>


            <fieldset class="panel panel-default panel-half first draggable" id="pagestoadd">
                <h3 class="panel-heading">
                    {str tag=addviewstocollection section=collection}
                </h3>
                <div class="panel-body">
                    {if $viewsform}
                        <div class="pull-right">
                            <a href="" id="selectall">{str tag=All}</a>&nbsp;&nbsp;
                            <a href="" id="selectnone">{str tag=none}</a>
                        </div>
                        {$viewsform|safe}
                    {else}
                        <div class="lead text-small">{str tag=noviewsavailable section=collection}</div>
                    {/if}
                </div>
            </fieldset>
            <fieldset class="panel panel-default panel-half droppable" id="pagesadded">
                <h3 class="panel-heading">{str tag=viewsincollection section=collection}</h3>
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

                                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                                        <span class="fa fa-long-arrow-down prs"></span>
                                    </a>

                                {elseif $view->displayorder == $views.max}

                                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                                        <span class="fa fa-long-arrow-up prs"></span>
                                    </a>

                                {else}

                                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=up">
                                        <span class="fa fa-long-arrow-up prs"></span>
                                    </a>
                                    <a href="{$displayurl}&amp;view={$view->view}&amp;direction=down">
                                        <span class="fa fa-long-arrow-down "></span>
                                    </a>
                                {/if}
                            {/if}
                            <strong>
                                <a href="{$view->fullurl}" class="text-link">
                                    {$view->title}
                                </a>
                            </strong>
                            {$view->remove|safe}
                        </li>

                    {/foreach}
                    </ol>

                {/if}
            </fieldset>
        </div>
    </div>
    <div id="collectiondonewrap">
        <a class="btn btn-success" href="{$baseurl}">{str tag=done}</a>
    </div>

{include file="footer.tpl"}
