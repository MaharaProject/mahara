<div class="pageheader matrixheader">
    <div class="pageheader-wrap">
        <div class="container pageheader-content">
            <div class="row">
                <div class="col-md-12 main">

                    {if $collection}
                        {include file=collectionnav.tpl}
                    {/if}
                    {if !($headertype == "matrix")}
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
                                    <a id="review_link" class="btn btn-secondary" href="#" data-bs-toggle="modal" data-bs-target="#review-form">
                                        <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                                        {str tag=objectionreview}
                                    </a>
                                </div>
                            </div>
                        {/if}
                    {/if}

                    {if $name}
                    <h1 id="viewh1" class="page-header">
                        <span class="section-heading">{$name}</span>
                        {if $collectiontitle}<span class="visually-hidden">{str tag=pageincollectiontitle section=collection arg1=$collectiontitle|safe}</span>{/if}
                        {if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}
                    </h1>
                    <div class="text-small">
                        {include file=author.tpl}
                    </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
    {include file="header/pageactions.tpl"}
</div>
