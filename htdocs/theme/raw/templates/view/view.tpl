{include file="header.tpl"}

{if $notrudeform}
    <div class="alert alert-danger">
    {$notrudeform|safe}
    </div>
{/if}

{if $maintitle}
<h1 id="viewh1" class="page-header">
    {$maintitle|safe}
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
            <span class="icon icon-pencil icon-lg prs"></span>
            {str tag=editthisview section=view}
        </a>
        {/if}
    {/strip}{/if}
    
    {if $copyurl}{strip}
    <a title="{str tag=copythisview section=view}" href="{$copyurl}" class="btn btn-default">
        <span class="text-success icon icon-files-o icon-lg prs"></span>
        {str tag=copy section=mahara}
    </a>
    {/strip}{/if}
    
    {if $mnethost}
    <a href="{$mnethost.url}" class="btn btn-default">
        <span class="icon icon-long-arrow-right icon-lg prs"></span>
        {str tag=backto arg1=$mnethost.name}
    </a>
    {/if}

    {if $LOGGEDIN}
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <span class="icon icon-ellipsis-h"></span>
            <span class="sr-only">{str tag="more..."}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right" role="menu">
            <li>
                <a id="toggle_watchlist_link" class="watchlist" href="">
                    {if $viewbeingwatched}
                    <span class="icon icon-eye-slash prs"></span>
                    {str tag=removefromwatchlist section=view}
                    {else}
                    <span class="icon icon-eye prs"></span>
                    {str tag=addtowatchlist section=view}
                    {/if}
                </a>
            </li>
            <li>
                <a id="objection_link" href="#" data-toggle="modal" data-target="#report-form">
                    <span class="icon icon-lg icon-flag text-danger prs"></span>
                    {str tag=reportobjectionablematerial}
                </a>
            </li>
        </ul>
    </div>
    {/if}
</div>

{if $collection}
{include file=collectionnav.tpl}
{/if}

<div class="ptxl">
    {assign var='author_link_index' value=1}
    {include file=author.tpl}

    {if $tags}
    <div class="tags pbl">
        <strong>{str tag=tags}:</strong> 
        {list_tags owner=$owner tags=$tags}
    </div>
    {/if}
</div>

<div id="view-description">{$viewdescription|clean_html|safe}</div>

<div id="view">
    <div id="bottom-pane">
        <div id="column-container">
            {$viewcontent|safe}
        </div>
    </div>
    <div class="viewfooter">
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
            {if $feedback->count || $enablecomments}
            <h3 class="title">
                {str tag="Comments" section="artefact.comment"}
            </h3>
            <hr>
            <div id="feedbacktable" class="feedbacktable fullwidth">
                {$feedback->tablerows|safe}
            </div>
            {$feedback->pagination|safe}
            {/if}
        {/if}
        
        <div id="viewmenu" class="view-menu ptxl pbl">
            {include file="view/viewmenu.tpl"}
            
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

{if $visitstring}
<div class="metadata text-right">
    {$visitstring}
</div>
{/if}
{include file="footer.tpl"}


