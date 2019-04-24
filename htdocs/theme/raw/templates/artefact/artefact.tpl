{include file="header.tpl"}

{if $notrudeform}
    <div class="message deletemessage alert alert-danger">
        {$notrudeform|safe}
    </div>
{elseif $objector}
    <div class="alert alert-danger">{str tag=objectionablematerialreported}</div>
{/if}
{if $userisowner && $objectedpage}
    <div class="alert alert-danger">
        <p>
        {if $objectionreplied}
            {str tag=objectionablematerialreportreplied}
        {else}
            {str tag=objectionablematerialreportedowner}
        {/if}
        </p>
        <p>{str tag=objectionreviewonview}</p>
    </div>
{/if}

<div class="row">
    <div class="col-lg-9">

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
                | {$viewdisplaytitle|safe}
                {if $hasfeed}
                <a href="{$feedlink}">
                    <span class="icon-rss icon float-right" role="presentation" aria-hidden="true"></span>
                </a>
                {/if}
            </span>
        </h1>

        <div class="btn-top-right btn-group btn-group-top float-right">
            {if $LOGGEDIN && (!$userisowner || ($userisowner && $objectedpage))}
            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="{str tag='moreoptions'}" aria-expanded="false">
                <span class="icon icon-ellipsis-h" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag="moreoptions"}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                <li class="dropdown-item">
                    {strip}
                    <a id="toggle_watchlist_link" class="watchlist" href="">
                        {if $viewbeingwatched}
                            <span class="icon icon-lg icon-eye-slash left" role="presentation" aria-hidden="true"></span>
                        {else}
                            <span class="icon icon-lg icon-eye left" role="presentation" aria-hidden="true"></span>
                        {/if}

                        {if $artefact}
                            {if $viewbeingwatched}
                                {str tag=removefromwatchlistartefact section=view arg1=$viewtitle}
                            {else}
                                {str tag=addtowatchlistartefact section=view arg1=$viewtitle}
                            {/if}
                        {else}
                            {if $viewbeingwatched}
                                {str tag=removefromwatchlist section=view}
                            {else}
                                {str tag=addtowatchlist section=view}
                            {/if}
                        {/if}
                    </a>
                    {/strip}
                </li>
                <li class="dropdown-item">
                    {strip}
                    {if $objector}
                    <span class="nolink">
                        <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                        {str tag=objectionablematerialreported}
                    </span>
                    {else}
                    <a id="objection_link" class="objection" href="#" data-toggle="modal" data-target="#report-form">
                        <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                        {str tag=reportobjectionablematerial}
                    </a>
                    {/if}
                    {/strip}
                </li>
                {if $userisowner && $objectedpage}
                    <li>
                    {strip}
                    <span class="nolink">
                        <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                        {str tag=objectionreviewonview}
                    </span>
                    {/strip}
                    </li>
                {/if}
            </ul>
            {/if}
        </div>

        <div id="view" class="view-pane">
            <div id="bottom-pane" class="card">
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
                            <button type="button" class="close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
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

{if $stillrudeform}
    {include file=objectionreview.tpl}
{/if}

{include file="footer.tpl"}
