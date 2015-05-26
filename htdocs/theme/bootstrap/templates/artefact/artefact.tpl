{include file="header.tpl"}

<div class="row">
    <div class="col-md-9">

        {if $notrudeform}<div class="message deletemessage">{$notrudeform|safe}</div>{/if}

        <h1 class="page-header">
            {$view->display_title()|safe}
            {foreach from=$artefactpath item=a}:
                {if $a.url}<a href="{$a.url}">{/if}
                {$a.title}
                {if $a.url}</a>{/if}
                {if $hasfeed}
                <a href="{$feedlink}"><span class="fa-rss fa pull-right"></span></a>
                {/if}
            {/foreach}
        </h1>

        <div id="view" class="view-pane">
            <div id="bottom-pane" class="panel panel-secondary">
                <div id="column-container" class="no-heading">
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
