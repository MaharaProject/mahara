{include file="header.tpl" headertype="page"}
{include file='modal-details.tpl'}

<input type="hidden" id="viewid" name="id" value="{$viewid}">
{if $viewinstructions}
<div id="viewinstructions" class="pageinstructions view-instructions first last form-group collapsible-group small-group {if $toolbarhtml}with-toolbar{/if}">
    <fieldset  class="pieform-fieldset collapsible collapsible-small">
        <legend>
            <a href="#viewinstructions-dropdown" data-bs-toggle="collapse" aria-expanded="false" aria-controls="viewinstructions-dropdown" class="collapsed">
                {str tag='instructions' section='view'}<span class="icon icon-chevron-down collapse-indicator right text-inline"></span>
            </a>
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
            {if $peerhidden}
            <div class="alert alert-info">
                {str tag=nopeerassessmentrequired section=artefact.peerassessment}
            </div>
            {/if}
            {if $signedoffalertpeermsg}
            <div class="alert alert-info">
                {$signedoffalertpeermsg|clean_html|safe}
            </div>
            {/if}
            <div class="grid-stack">
            {if $viewcontent}
                {$viewcontent|safe}
            {/if}
            </div>
        </div>
    </div>
    <div class="view-container{if $feedback->position eq 'base' || $releaseform || $view_group_submission_form || $ltisubmissionform} viewfooter{/if}">
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

        {if $feedback->position eq 'base' && $feedback->baseplacement}
        <div class="comment-container collapsible-group">
            {if $feedback->count || $enablecomments}
            <fieldset class="pieform-fieldset first last collapsible">
                <legend>
                    <button href="#dropdown" data-bs-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                        <span class="icon icon-comments left" role="presentation" aria-hidden="true"></span>
                        <span id="comment-section-title">{if $feedback->count}{str tag="Comments" section="artefact.comment"}{else}{str tag=addcomment section=artefact.comment}{/if}</span>
                        <span id="comment-section-count" class="text-small">{if $feedback->count}({$feedback->count}){/if}</span>
                        <span class="icon icon-chevron-down collapse-indicator right float-end" role="presentation" aria-hidden="true"></span>
                    </button>
                </legend>
                <div class="fieldset-body comment-fieldset-body collapse" id="dropdown">
                {* Do not change the id because it is used by paginator.js *}
                    <div id="feedbacktable{if $blockid}_{$blockid}{/if}" class="feedbacktable js-feedbackbase fullwidth">
                    {$feedback->tablerows|safe}
                    </div>
                    {$feedback->pagination|safe}
                    {if $enablecomments}
                        {include file="view/viewmenu.tpl"}
                    {/if}
                </div>
            </fieldset>
            {/if}
        </div>
        {/if}
        {if $feedback->position eq 'blockinstance' && $enablecomments}
        <div class="feedback modal modal-docked" id="feedback-form">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="btn-close" data-bs-dismiss="modal-docked" aria-label="{str tag=Close}">
                            <span class="times">&times;</span>
                            <span class="visually-hidden">{str tag=Close}</span>
                        </button>
                        <h1 class="modal-title">
                        <span class="icon icon-comments left" role="presentation" aria-hidden="true"></span>
                        {str tag=addcomment section=artefact.comment}
                        </h1>
                    </div>
                    <div id="comment_modal_messages"></div>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                        <h1 class="modal-title">
                            <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=reportobjectionablematerial}
                        </h1>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                        <h1 class="modal-title">
                            <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=objectionreview}
                        </h1>
                    </div>
                    <div class="modal-body">
                        {$reviewform|safe}
                    </div>
                </div>
            </div>
        </div>
        {/if}
        {if $revokeaccessform}
        <div class="modal fade" id="revokemyaccess-form">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h1 class="modal-title">
                            <span class="icon icon-trash-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=revokemyaccessformtitle section=collection}
                        </h1>
                    </div>
                    <div class="modal-body">
                        <div class="description">{str tag=revokemyaccessdescription section=collection}</div>
                        {$revokeaccessform|safe}
                    </div>
                </div>
            </div>
        </div>
        {/if}
        <div class="modal fade" id="copyview-form">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                        <h1 class="modal-title">
                            <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=confirmcopytitle section=view}
                        </h1>
                    </div>
                    <div class="modal-body">
                        <p>{str tag=confirmcopydesc section=view}</p>
                        <div class="btn-group">
                            <button id="copy-collection-button" type="button" class="btn btn-secondary"><span class="icon icon-folder-open" role="presentation" aria-hidden="true"></span> {str tag=Collection section=collection}</button>
                            <button id="copy-view-button" type="button" class="btn btn-secondary"><span class="icon icon-regular icon-file-alt" role="presentation" aria-hidden="true"></span> {str tag=view}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="metadata text-end last-updated">
    {$lastupdatedstr}{if $visitstring}; {$visitstring}{/if}
</div>

{if $stillrudeform}
    {include file=objectionreview.tpl}
{/if}

{include file="footer.tpl"}
