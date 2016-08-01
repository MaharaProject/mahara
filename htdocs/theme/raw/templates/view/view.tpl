{include file="header.tpl"}

{if $collection}
    {include file=collectionnav.tpl}
{/if}

{if $notrudeform}
    <div class="alert alert-danger">
    {$notrudeform|safe}
    </div>
{/if}

{if $maintitle}
<h1 id="viewh1" class="page-header">
    {if $title}
        <span class="subsection-heading">{$title|safe}</span>
    {else}
        <span class="section-heading">{$maintitle|safe}</span>
    {/if}
</h1>
{/if}

<div class="btn-group btn-group-top">
    {if $editurl}{strip}
        {if $new}
        <a class="btn btn-default" href="{$editurl}">
            {str tag=back}
        </a>
        {else}
        <a title="{str tag=editthisview section=view}" href="{$editurl}" class="btn btn-default">
            <span class="icon icon-pencil icon-lg left" role="presentation" aria-hidden="true"></span>
            {str tag=editthisview section=view}
        </a>
        {/if}
    {/strip}{/if}

    {if $copyurl}{strip}
    <a id="copyview-button" title="{str tag=copythisview section=view}" href="{$copyurl}" class="btn btn-default">
        <span class="icon icon-files-o icon-lg left" role="presentation" aria-hidden="true"></span>
        {str tag=copy section=mahara}
    </a>
    {/strip}{/if}

    {if $mnethost}
    <a href="{$mnethost.url}" class="btn btn-default">
        <span class="icon icon-long-arrow-right icon-lg left" role="presentation" aria-hidden="true"></span>
        {str tag=backto arg1=$mnethost.name}
    </a>
    {/if}

    {if $LOGGEDIN && !$userisowner}
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <span class="icon icon-ellipsis-h icon-lg" role="presentation" aria-hidden="true"></span>
        <span class="sr-only">{str tag="more..."}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right" role="menu">
        <li>
            <a id="toggle_watchlist_link" class="watchlist" href="">
                {if $viewbeingwatched}
                <span class="icon icon-eye-slash left" role="presentation" aria-hidden="true"></span>
                {str tag=removefromwatchlist section=view}
                {else}
                <span class="icon icon-eye left" role="presentation" aria-hidden="true"></span>
                {str tag=addtowatchlist section=view}
                {/if}
            </a>
        </li>
        <li>
            <a id="objection_link" href="#" data-toggle="modal" data-target="#report-form">
                <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                {str tag=reportobjectionablematerial}
            </a>
        </li>
    </ul>
    {/if}
</div>

<div class="with-heading text-small">
    {include file=author.tpl}

    {if $tags}
    <div class="tags">
        <strong>{str tag=tags}:</strong>
        {list_tags owner=$owner tags=$tags}
    </div>
    {/if}
</div>

<div id="view-description" class="view-description">
    {$viewdescription|clean_html|safe}
</div>

<div id="view" class="view-container">
    <div id="bottom-pane">
        <div id="column-container" class="user-page-content">
            {$viewcontent|safe}
        </div>
    </div>
    <div class="viewfooter view-container">
        {if $releaseform}
        <div class="releaseviewform alert alert-warning clearfix">
            {$releaseform|safe}
        </div>
        {/if}

        {if $view_group_submission_form}
        <div class="submissionform alert alert-default">
            {$view_group_submission_form|safe}
        </div>
        {/if}

        {if $feedback->position eq 'base'}
        <div class="comment-container">
            {if $feedback->count || $enablecomments}
            <h3 class="title">
                {str tag="Comments" section="artefact.comment"}
            </h3>
            {if $feedback->count == 0}
            <hr />
            {/if}
            {* Do not change the id because it is used by paginator.js *}
            <div id="feedbacktable" class="feedbacktable js-feedbackbase fullwidth">
                {$feedback->tablerows|safe}
            </div>
            {$feedback->pagination|safe}
            {/if}

            {if $enablecomments}
                {include file="view/viewmenu.tpl"}
            {/if}
        </div>
        {/if}

        {if $feedback->position eq 'blockinstance' && $enablecomments}
        <div class="feedback modal modal-docked" id="feedback-form">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal-docked" aria-label="Close">
                            <span class="times">&times;</span>
                            <span class="sr-only">{str tag=Close}</span>
                        </button>
                        <h4 class="modal-title">
                            <span class="icon icon-lg icon-comments left" role="presentation" aria-hidden="true"></span>
                            {str tag=addcomment section=artefact.comment}
                        </h4>
                    </div>
                    <div class="modal-body">
                        {$addfeedbackform|safe}
                    </div>
                </div>
            </div>
        </div>
        {/if}

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
        <div class="modal fade" id="copyview-form">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=confirmcopytitle section=view}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>{str tag=confirmcopydesc section=view}</p>
                        <div class="btn-group">
                            <button id="copy-collection-button" type="button" class="btn btn-default"><span class="icon icon-folder-open" role="presentation" aria-hidden="true"></span> {str tag=Collection section=collection}</button>
                            <button id="copy-view-button" type="button" class="btn btn-default"><span class="icon icon-file-text " role="presentation" aria-hidden="true"></span> {str tag=view}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{if $visitstring}
<div class="metadata text-right">
    {$visitstring}
</div>
{/if}
{include file="footer.tpl"}
