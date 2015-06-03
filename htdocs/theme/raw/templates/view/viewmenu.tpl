<div class="pull-right">
    {contextualhelp plugintype='core' pluginname='view' section='viewmenu'}
</div>

<ul class="nav nav-tabs ">
    {if $feedback->position eq 'base' && $enablecomments}
        <li class="active">
            <a id="add_feedback_link" class="feedback" href="#comment-form" aria-controls="comment-form" role="tab" data-toggle="tab">
                <span class="fa fa-lg fa-comments prm"></span>
                {str tag=Comment section=artefact.comment}
            </a>
        </li>
        {if $LOGGEDIN}
        <li>
            <a id="objection_link" class="objection" href="#report-form" role="tab" aria-controls="report-form" data-toggle="tab">
                <span class="fa fa-lg fa-flag prs"></span>
                {str tag=reportobjectionablematerial}
            </a>
        </li>
        {/if}
    {else}
        {if $LOGGEDIN}
        <li class="active">
            <a id="objection_link" class="objection" href="#report-form" role="tab" aria-controls="report-form" data-toggle="tab">
                <span class="fa fa-lg fa-flag prs"></span>
                {str tag=reportobjectionablematerial}
            </a>
        </li>
        {/if}
    {/if}
</ul>

