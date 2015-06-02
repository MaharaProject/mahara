{include file="header.tpl"}

<div class="row">
    <div class="col-md-9">

        {if $notrudeform}
        <div class="message deletemessage">
            {$notrudeform|safe}
        </div>
        {/if}

        
        <div class="text-thin pbl">
            <span>
                {$view->display_title()|safe}
            </span>
            {if $hasfeed}
            <a href="{$feedlink}">
                <span class="fa-rss fa pull-right"></span>
            </a>
            {/if}
        </div>
        
        <h1 class="page-header">
            {foreach from=$artefactpath item=a name='path'}
                <span class="subsection-heading">
                    {if $a.url}
                        {if $.foreach.path.total == 1}
                            {$a.title}
                        {elseif $.foreach.path.last}
                            | {$a.title}
                        {else}
                            <a href="{$a.url}">
                                {$a.title}
                            </a> 
                        {/if}
                    {else}
                        {$a.title}
                    {/if}
                </span>
            {/foreach}
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

<!--             <a id="print_link" class="print btn btn-sm btn-default" href="" onclick="window.print(); return false;">
                <span class="fa fa-lg fa-print prs"></span> 
                {str tag=print section=view}
            </a> -->
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
            <div class="tab-content">
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
