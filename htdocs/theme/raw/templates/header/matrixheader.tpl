<div class="pageheader matrixheader">
    <div class="container pageheader-content">
        <div class="row">
            <div class="col-md-12 main">

                {if $collection}
                    {include file=collectionnav.tpl}
                {/if}

                <h1 id="viewh1" class="page-header">
                    <span class="section-heading">{$name}</span>
                </h1>
                <div class="with-heading text-small">
                    {include file=author.tpl}
                </div>

            </div>
        </div>
    </div>
</div>
