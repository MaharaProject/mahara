{include file="header.tpl"}

<div class="row">
    <div class="col-md-9">

        {if $notrudeform}
        <div class="message deletemessage alert alert-danger">
            {$notrudeform|safe}
        </div>
        {/if}

        <h1 class="page-header">
            {if count($artefactpath) == 0}
                {$artefacttitle}
            {else}
                {foreach from=$artefactpath item=a name='path'}
                    <span class="lead text-small">
                        <a href="{$a.url}">
                            {$a.title}
                        </a> /
                    </span>
                {/foreach}
                <br>
                <span class="subsection-heading">{$artefacttitle}</span>
            {/if}
            <span class="metadata">
                | {$view->display_title()|safe}
                {if $hasfeed}
                <a href="{$feedlink}">
                    <span class="icon-rss icon pull-right" role="presentation" aria-hidden="true"></span>
                </a>
                {/if}
            </span>
        </h1>

        <div class="btn-top-right btn-group btn-group-top pull-right">
            {if $LOGGEDIN}
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="icon icon-ellipsis-h" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag="more..."}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li>
                    <a id="toggle_watchlist_link" class="watchlist" href="">

                        {if $viewbeingwatched}
                            <span class="icon icon-eye-slash left" role="presentation" aria-hidden="true"></span>
                        {else}
                            <span class="icon icon-eye left" role="presentation" aria-hidden="true"></span>
                        {/if}

                        {if $artefact}
                            {if $viewbeingwatched}
                                {str tag=removefromwatchlistartefact section=view arg1=$view->get('title')}
                            {else}
                                {str tag=addtowatchlistartefact section=view arg1=$view->get('title')}
                            {/if}
                        {else}
                            {if $viewbeingwatched}
                                {str tag=removefromwatchlist section=view}
                            {else}
                                {str tag=addtowatchlist section=view}
                            {/if}
                        {/if}
                    </a>
                </li>
                <li>
                    <a id="objection_link" class="objection" href="#" data-toggle="modal" data-target="#report-form">
                        <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                        {str tag=reportobjectionablematerial}
                    </a>
                </li>
            {/if}
        </div>

        <div id="view" class="view-pane">
            <div id="bottom-pane" class="panel panel-secondary">
                <div id="column-container" class="no-heading view-container">
                {$artefact|safe}
                </div>
            </div>
        </div>

        <div class="viewfooter view-container">
            <div class="comment-container">
                {if $feedback->count || $enablecomments}
                    <h3 class="title">{str tag="Comments" section="artefact.comment"}</h3>
                    {* Do not change the id because it is used by paginator.js *}
                    <div id="feedbacktable" class="feedbacktable commentlist js-feedbackbase">
                        {$feedback->tablerows|safe}
                    </div>
                    {$feedback->pagination|safe}
                {/if}
                <div id="viewmenu" class="view-menu">
                    {include file="view/viewmenuartefact.tpl"}
                </div>
            </div>

            {if $LOGGEDIN}
            <div class="modal fade" id="report-form">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">
                                <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                                {str tag=reportobjectionablematerial}
                            </h4>
                        </div>
                        <div class="modal-body">
                            {$objectionform|safe}
                        </div>
                    </div>
                </div>
            </div>
            {/if}
        </div>
    </div>
</div>

{include file="footer.tpl"}
