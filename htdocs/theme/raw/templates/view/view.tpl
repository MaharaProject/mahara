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

{if !$microheaders && ($mnethost || $editurl)}
<div class="btn-group btn-group-top">
    {if $LOGGEDIN}
    <a id="toggle_watchlist_link" class="watchlist btn btn-default" href="">
        {if $viewbeingwatched}
            <span class="fa fa-eye-slash prs"></span>
            {str tag=removefromwatchlist section=view}
        {else}
            <span class="fa fa-eye prs"></span>
            {str tag=addtowatchlist section=view}
        {/if}
    </a>
    {/if}
    
    {if $editurl}{strip}
        {if $new}
        <a class="btn btn-default" href="{$editurl}">
            {str tag=back}
        </a>
        {else}
        <a title="{str tag=editthisview section=view}" href="{$editurl}" class="btn btn-default">
            <span class="fa fa-pencil fa-lg prs"></span>
            {str tag=editthisview section=view}
        </a>
        {/if}
    {/strip}{/if}
    
    {if $copyurl}{strip}
    <a title="{str tag=copythisview section=view}" href="{$copyurl}" class="btn btn-default">
        <span class="text-success fa fa-files-o fa-lg prs"></span>
        {str tag=copy section=mahara}
    </a>
    {/strip}{/if}
    
    {if $mnethost}
    <a href="{$mnethost.url}" class="btn btn-default">
        <span class="fa fa-long-arrow-right fa-lg prs"></span>
        {str tag=backto arg1=$mnethost.name}
    </a>
    {/if}
</div>
{/if}

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
            
            <div id="feedbacktable" class="feedbacktable fullwidth">
                {$feedback->tablerows|safe}
            </div>
            {$feedback->pagination|safe}
            {/if}
        {/if}
        
        <div id="viewmenu" class="view-menu ptxl pbl">
            {include file="view/viewmenu.tpl"}
            {if $feedback->position eq 'base' && $enablecomments}
            <div class="tab-content">
                 <div id="comment-form" role="tabpanel" class="tab-pane active">
                    {$addfeedbackform|safe}
                </div>
                {if $LOGGEDIN}
                 <div id="report-form" role="tabpanel" class="tab-pane">
                    {$objectionform|safe}
                </div>
                {/if}
            </div>
            {else}
                {if $LOGGEDIN}
                 <div id="report-form">
                    {$objectionform|safe}
                </div>
                {/if}
            {/if}
        </div>
    </div>
</div>

{if $visitstring}
<div class="ctime s text-thin">
    {$visitstring}
</div>
{/if}
{include file="footer.tpl"}


