<div class="pageactions">
    <div class="btn-group btn-group-top">
        {if $editurl}{strip}
            <a title="{str tag=editthisview section=view}" href="{$editurl}" class="btn btn-secondary">
                <span class="icon icon-pencil icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=editthisview section=view}</span>
            </a>
        {/strip}{/if}

        {if $mnethost}
        <a href="{$mnethost.url}" class="btn btn-secondary">
            <span class="icon icon-long-arrow-right icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag=backto arg1=$mnethost.name}</span>
        </a>
        {/if}

        <button type="button" class="btn btn-secondary dropdown-toggle" title="{str tag='moreoptions'}" data-toggle="dropdown" aria-expanded="false">
            <span class="icon icon-ellipsis-h icon-lg" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag="more..."}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right" role="menu">

            {if $copyurl}{strip}
                <li class="dropdown-item">
                    {if $downloadurl}
                        <a id="downloadview-button" title="{str tag=copythisview section=view}" href="{$downloadurl}">
                    {else}
                        <a id="copyview-button" title="{str tag=copythisview section=view}" href="{$copyurl}">
                    {/if}
                    <span class="icon icon-files-o icon-lg left" role="presentation" aria-hidden="true"></span>
                    {str tag=copy section=mahara}
                    </a>
                </li>
            {/strip}{/if}

            <li class="dropdown-item">
                <a title="{str tag=print section=view}" id="print_link" href="#" onclick="window.print(); return false;">
                    <span class="icon icon-print left" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=print section=view}</span>
                </a>
            </li>
            {if $LOGGEDIN}
                {if !$userisowner}
                <li class="dropdown-item">
                    <a id="toggle_watchlist_link" class="watchlist" href="">
                        {if $viewbeingwatched}
                       <span class="icon icon-eye-slash left" role="presentation" aria-hidden="true"></span>
                        {str tag=removefromwatchlist section=view}
                        {else}
                        <span class="icon icon-eye left" role="presentation" aria-hidden="true"></span>
                        {str tag=addtowatchlist section=view}
                       {/if}
                    </a>
                </li>
                <li class="dropdown-item">
                    {if $objector}
                        <span class="nolink">
                            <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=objectionablematerialreported}
                        </span>
                    {else}
                        <a id="objection_link" href="#" data-toggle="modal" data-target="#report-form">
                            <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            {str tag=reportobjectionablematerial}
                        </a>
                    {/if}
                </li>
                {/if}
                {if $userisowner && $objectedpage}
                <li>
                    <a id="review_link" href="#" data-toggle="modal" data-target="#review-form">
                        <span class="icon icon-lg icon-flag text-success left" role="presentation" aria-hidden="true"></span>
                        {str tag=objectionreview}
                    </a>
                </li>
                {/if}
                {if $userisowner || $canremove}
                <li class="dropdown-item">
                    <a href="{$WWWROOT}view/delete.php?id={$viewid}" title="{str tag=deletethisview section=view}">
                        <span class="icon icon-lg icon-trash text-danger" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str(tag=deletespecific arg1=$maintitle)|escape:html|safe}</span>
                        {str tag=deletethisview section=view}
                    </a>
                </li>
                {/if}
            {/if}
            {if $versionurl}
                <li>
                  <a href="{$versionurl}">
                      <span class="icon icon-code-fork icon-lg left" role="presentation" aria-hidden="true"></span>
                      <span class="sr-only">{str(tag=timelinespecific section=view arg1=$maintitle)|escape:html|safe}</span>
                      {str tag=timeline section=view}
                  </a>
                </li>
            {/if}
            {if $userisowner}
                <li>
                  <a href="{$createversionurl}">
                      <span class="icon icon-save icon-lg left" role="presentation" aria-hidden="true"></span>
                      <span class="sr-only">{str(tag=savetimelinespecific section=view arg1=$maintitle)|escape:html|safe}</span>
                      {str tag=savetimeline section=view}
                  </a>
                </li>
            {/if}
        </ul>
    </div>
</div>
