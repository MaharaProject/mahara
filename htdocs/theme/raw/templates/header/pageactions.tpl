<div class="pageactions">
    <div class="btn-group-vertical">

        {if !$peerroleonly && !($headertype == "matrix" || $headertype == "progresscompletion")}
        {* Assess button that will show/ hide comments and details in block-comments-details-header.tpl *}
        <button id="details-btn" type="button" class="btn btn-secondary" title="{str tag=detailslinkalt section=view}">
            <span class="icon icon-search-plus icon-lg left" role="presentation" aria-hidden="true" ></span>
            <span class="sr-only">{str tag=detailslinkalt section=view}</span>
        </button>
        {/if}

        {if $editurl}{strip}
            <a title="{str tag=editthisview section=view}" href="{$editurl}" class="btn btn-secondary">
                <span class="icon icon-pencil-alt icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=editthisview section=view}</span>
            </a>
        {/strip}{/if}

        {if $mnethost}
        <a href="{$mnethost.url}" class="btn btn-secondary" title="{str tag=backto arg1=$mnethost.name}">
            <span class="icon icon-long-arrow-alt-right icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag=backto arg1=$mnethost.name}</span>
        </a>
        {/if}

        <button type="button" class="btn btn-secondary dropdown-toggle" title="{str tag='moreoptions'}" data-toggle="dropdown" aria-expanded="false">
            <span class="icon icon-ellipsis-h icon-lg" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag="moreoptions"}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right" role="menu">

            {if $copyurl}{strip}
                <li class="dropdown-item">
                    {if $downloadurl}
                        <a id="downloadview-button" title="{str tag=copythisportfolio section=view}" href="{$downloadurl}">
                    {else}
                        <a id="copyview-button{if $headertype == "progresscompletion"}-progress{/if}" title="{str tag=copythisportfolio section=view}" href="{$copyurl}">
                    {/if}
                    <span class="icon icon-regular icon-copy left" role="presentation" aria-hidden="true"></span>
                    {str tag=copy section=mahara}
                    </a>
                </li>
            {/strip}{/if}

            {if $usercaneditview}
                <li class="dropdown-item">
                    <a id="" title="{str tag=manageaccess section=view}" href="{$accessurl}">
                        <span class="icon {if $viewlocked}icon-lock{else}icon-unlock{/if} left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">{str tag=manageaccess section=view}</span>
                    </a>
                </li>
           {/if}

            <li class="dropdown-item">
                <a title="{str tag=print section=view}" id="print_link" href="#" onclick="window.print(); return false;">
                    <span class="icon icon-print left" role="presentation" aria-hidden="true"></span>
                    <span class="link-text">{str tag=print section=view}</span>
                    <span class="sr-only">{str tag=print section=view}</span>
                </a>
            </li>

            {if $LOGGEDIN}
                {if !$userisowner}
                    {if !($headertype == "matrix" || $headertype == "progresscompletion")}
                    <li class="dropdown-item">
                        <a id="toggle_watchlist_link" class="watchlist" href="">
                        {if $viewbeingwatched}
                            <span class="icon icon-regular icon-eye-slash left" role="presentation" aria-hidden="true"></span>
                            <span class="link-text">{str tag=removefromwatchlist section=view}</span>
                            {else}
                            <span class="icon icon-regular icon-eye left" role="presentation" aria-hidden="true"></span>
                            <span class="link-text">{str tag=addtowatchlist section=view}</span>
                        {/if}
                        </a>
                    </li>
                    {/if}
                    {if !($headertype == "matrix")}
                    <li class="dropdown-item">
                        {if !($headertype == "matrix" || $headertype == "progresscompletion")}
                            {if $objector}
                                <span class="nolink">
                                    <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                                    <span class="link-text">{str tag=objectionablematerialreported}</span>
                                </span>
                            {else}
                                <a id="objection_link" href="#" data-toggle="modal" data-target="#report-form">
                                    <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                                    <span class="link-text">{str tag=reportobjectionablematerial}</span>
                                </a>
                            {/if}
                        {/if}
                    </li>
                    {/if}
                {/if}
                {if $undoverificationform}
                    <li class="dropdown-item">
                        <a id="undoverificationlink" href="#" data-toggle="modal" data-target="#undoverification-form">
                            <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                            <span class="link-text">{str tag=undoverification section=collection}</span>
                        </a>
                    </li>
                {/if}
                {if $revokeaccessform}
                    <li class="dropdown-item">
                        <a id="revokeaccesslink" href="#" data-toggle="modal" data-target="#revokemyaccess-form">
                            <span class="icon icon-trash-alt text-danger left" role="presentation" aria-hidden="true"></span>
                            <span class="link-text">{str tag=removeaccess}</span>
                        </a>
                    </li>
                {/if}
                {if $userisowner && $objectedpage}
                <li class="dropdown-item">
                    <a id="review_link" href="#" data-toggle="modal" data-target="#review-form">
                        <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">{str tag=objectionreview}</span>
                    </a>
                </li>
                {/if}
                {if ($userisowner || $canremove) && !($headertype == "matrix" || $headertype == "progresscompletion")}
                <li class="dropdown-item">
                    <a href="{$WWWROOT}view/delete.php?id={$viewid}" title="{str tag=deletethisview section=view}">
                        <span class="icon icon-trash-alt text-danger left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">{str tag=deletethisview section=view}</span>
                        <span class="sr-only">{str(tag=deletespecific arg1=$maintitle)|escape:html|safe}</span>
                    </a>
                </li>
                {/if}
            {/if}
            {if $versionurl}
                <li class="dropdown-item">
                  <a href="{$versionurl}">
                      <span class="icon icon-history left" role="presentation" aria-hidden="true"></span>
                      <span class="link-text">{str tag=timeline section=view}</span>
                      <span class="sr-only">{str(tag=timelinespecific section=view arg1=$maintitle)|escape:html|safe}</span>
                  </a>
                </li>
            {/if}
            {if $userisowner && !($headertype == "matrix" || $headertype == "progresscompletion") }
                <li class="dropdown-item">
                  <a href="{$createversionurl}">
                      <span class="icon icon-save left" role="presentation" aria-hidden="true"></span>
                      <span class="link-text">{str tag=savetimeline section=view}</span>
                      <span class="sr-only">{str(tag=savetimelinespecific section=view arg1=$maintitle)|escape:html|safe}</span>
                  </a>
                </li>
            {/if}
        </ul>
    </div>
</div>
