<div class="pageheader matrixheader">
    <div class="container pageheader-content">
        <div class="row">
            <div class="col-md-12 main">

                {if $collection}
                    {include file=collectionnav.tpl}
                {/if}

                {if $name}
                <h1 id="viewh1" class="page-header">
                    <span class="section-heading">{$name}</span>
                    {if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}
                </h1>
                <div class="text-small font-weight-thin">
                    {include file=author.tpl}
                </div>
                {/if}
            </div>
        </div>
    </div>
</div>
