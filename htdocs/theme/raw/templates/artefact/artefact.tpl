{include file="header.tpl"}

<div class="row">
    <div class="col-md-9">

        {if $notrudeform}
        <div class="message deletemessage">
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
                    <span class="fa-rss fa pull-right"></span>
                </a>
                {/if}
            </span>
        </h1>

        <div class="text-right btn-top-right btn-group btn-group-top pull-right">
            {if $LOGGEDIN}
                <a id="toggle_watchlist_link" class="watchlist btn btn-sm btn-default" href="">

                    {if $viewbeingwatched}
                        <span class="fa fa-eye-slash prs"></span>
                    {else}
                        <span class="fa fa-eye prs"></span>
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
            
            <div class="tab-content pt0">
                 <div id="comment-form" role="tabpanel" class="tab-pane active">
                    {$addfeedbackform|safe}
                </div>

                 <div id="report-form" role="tabpanel" class="tab-pane">
                    {$objectionform|safe}
                </div>
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}
