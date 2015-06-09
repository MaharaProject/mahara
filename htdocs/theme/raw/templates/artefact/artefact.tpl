{include file="header.tpl"}

<div class="row">
    <div class="col-md-9">

        {if $notrudeform}
        <div class="message deletemessage alert alert-danger">
            {$notrudeform|safe}
        </div>
        {/if}

        <h1 class="page-header ptl">
            {foreach from=$artefactpath item=a name='path'}
                {if $a.url}
                    {if $.foreach.path.total == 1}
                        {$a.title}
                    {elseif $.foreach.path.last}
                        <br />
                        <span class="subsection-heading">
                            {$a.title}
                        </span>
                    {else}
                        <span class="lead text-small ptl">
                            <a href="{$a.url}">
                                {$a.title}
                            </a> /
                        </span>
                    {/if}
                {else}
                    {$a.title}
                {/if}
            {/foreach}
            <!-- <br /> -->
            <span class="metadata">
            <!-- <span class="section-heading"> -->
                | {$view->display_title()|safe}
                {if $hasfeed}
                <a href="{$feedlink}">
                    <span class="icon-rss icon pull-right"></span>
                </a>
                {/if}
            </span>
        </h1>

        <div class="text-right btn-top-right btn-group btn-group-top pull-right">
            {if $LOGGEDIN}
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="icon icon-ellipsis-h icon-lg"></span>
                <span class="sr-only">More options</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li>
                    <a id="toggle_watchlist_link" class="watchlist" href="">

                        {if $viewbeingwatched}
                            <span class="icon icon-eye-slash prs"></span>
                        {else}
                            <span class="icon icon-eye prs"></span>
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
                        <span class="icon icon-lg icon-flag text-danger prs"></span>
                        {str tag=reportobjectionablematerial}
                    </a>
                </li>
            {/if}
        </div>

        <div id="view" class="view-pane">
            <div id="bottom-pane" class="panel panel-secondary">
                <div id="column-container" class="no-heading ptl">
                {$artefact|safe}
                </div>
            </div>
        </div>

        <div class="viewfooter ptxl">
            {if $feedback->count || $enablecomments}
                <h4 class="title">{str tag="Comments" section="artefact.comment"}</h4>
                <hr />

                <div id="commentlist" class="commentlist">
                    {$feedback->tablerows|safe}
                </div>

                {$feedback->pagination|safe}

            {/if}
            <div id="viewmenu" class="view-menu">
                {include file="view/viewmenuartefact.tpl"}
            </div>

            {if $LOGGEDIN}
            <div class="modal fade" id="report-form">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">
                                <span class="icon icon-lg icon-flag text-danger prs"></span>
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
