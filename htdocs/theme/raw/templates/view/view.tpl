{include file="header.tpl" headertype="page"}

<div id="view-description" class="view-description {if $toolbarhtml}with-toolbar{/if}">
    {$viewdescription|clean_html|safe}
</div>

{if $viewinstructions}
    <div id="viewinstructions" class="pageinstructions view-instructions last form-group collapsible-group small-group {if $toolbarhtml}with-toolbar{/if}">
    <fieldset  class="pieform-fieldset collapsible collapsible-small">
        <legend>
            <h4>
                <a href="#viewinstructions-dropdown" data-toggle="collapse" aria-expanded="false" aria-controls="viewinstructions-dropdown" class="collapsed">
                    {str tag='instructions' section='view'}
                    <span class="icon icon-chevron-down collapse-indicator right text-inline"></span>
                </a>
            </h4>
        </legend>
        <div class="viewinstructions fieldset-body collapse" id="viewinstructions-dropdown">
            {$viewinstructions|clean_html|safe}
        </div>
    </fieldset>
    </div>
{/if}

<div id="view" class="view-container">
    <div id="bottom-pane">
        <div id="column-container" class="user-page-content">
            {if $viewcontent}
                {$viewcontent|safe}
            {else}
                <div class="alert alert-info">
                    <span class="icon icon-lg icon-info-circle left" role="presentation" aria-hidden="true"></span>
                    {str tag=nopeerassessmentrequired section=artefact.peerassessment}
                </div>
            {/if}
        </div>
    </div>
    <div class="viewfooter view-container">
        {if $releaseform}
        <div class="releaseviewform alert alert-submitted clearfix">
            {$releaseform|safe}
        </div>
        {/if}

        {if $view_group_submission_form}
        <div class="submissionform alert alert-default">
            {$view_group_submission_form|safe}
        </div>
        {/if}

        {if $ltisubmissionform}
        <div class="submissionform alert alert-default">
            {$ltisubmissionform|safe}
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
                        <button class="close" data-dismiss="modal-docked" aria-label="{str tag=Close}">
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
        {if $userisowner}
        <div class="modal fade" id="review-form">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=objectionreview}
                        </h4>
                    </div>
                    <div class="modal-body">
                        {$reviewform|safe}
                    </div>
                </div>
            </div>
        </div>
        {/if}
        <div class="modal fade" id="copyview-form">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">
                            <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=confirmcopytitle section=view}
                        </h4>
                    </div>
                    <div class="modal-body">
                        <p>{str tag=confirmcopydesc section=view}</p>
                        <div class="btn-group">
                            <button id="copy-collection-button" type="button" class="btn btn-secondary"><span class="icon icon-folder-open" role="presentation" aria-hidden="true"></span> {str tag=Collection section=collection}</button>
                            <button id="copy-view-button" type="button" class="btn btn-secondary"><span class="icon icon-file-text " role="presentation" aria-hidden="true"></span> {str tag=view}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="metadata text-right">
    {$lastupdatedstr}{if $visitstring}; {$visitstring}{/if}
</div>

{if $stillrudeform}
    {include file=objectionreview.tpl}
{/if}

{include file="footer.tpl"}
