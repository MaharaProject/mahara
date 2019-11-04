<a id="header-target-main"></a>
<div id="header-content" class="pageheader pageheader-actions">
    <div class="container pageheader-content">
        <div class="row">
            <div class="col-md-12 main">
                <div class="main-column{if $selected == 'content'} editcontent{/if}">
                    <div id="pageheader-column-container">
                        {if $collection}
                            {include file=collectionnav.tpl}
                        {/if}

                        {if $notrudeform}
                            <div class="alert alert-danger">
                            {$notrudeform|safe}
                            </div>
                        {elseif $objector}
                            <div class="alert alert-danger">{str tag=objectionablematerialreported}</div>
                        {/if}
                        {if $userisowner && $objectedpage}
                            <div class="alert alert-danger">
                                {if $objectionreplied}
                                    {str tag=objectionablematerialreportreplied}
                                {else}
                                    {str tag=objectionablematerialreportedowner}
                                    <br><br>
                                    {str tag=objectionablematerialreportedreply}
                                {/if}
                                <div class="form-group">
                                <a id="review_link" class="btn btn-secondary" href="#" data-toggle="modal" data-target="#review-form">
                                    <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                                    {str tag=objectionreview}
                                </a>
                                </div>
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

                        <div class="btn-group-top-below">
                            {if $toolbarhtml}
                                {$toolbarhtml|safe}
                            {/if}
                        </div>

                        <div class="text-small">
                            {include file=author.tpl}

                            {if $alltags}
                            <div class="tags">
                                <strong>{str tag=tags}:</strong>
                                {list_tags owner=$owner tags=$alltags view=$viewid}
                                {if $moretags}
                                    <a href="#" class="moretags">
                                    <span class="icon icon-ellipsis-h" role="presentation" aria-hidden="true"></span>
                                    <span class="sr-only">{str tag="more..."}</span>
                                    </a>
                                {/if}
                            </div>
                            {/if}
                        </div>

                        {include file="header/pageactions.tpl"}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
