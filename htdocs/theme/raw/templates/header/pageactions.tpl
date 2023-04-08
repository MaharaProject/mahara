<div class="pageactions">
    <div class="btn-group-vertical">

        {if !$peerroleonly && !($headertype == "matrix" || $headertype == "progresscompletion" || $headertype == "outcomeoverview")}
        {* Assess button that will show/ hide comments and details in block-comments-details-header.tpl *}
        <button id="details-btn" type="button" class="btn btn-secondary" title="{str tag=detailslinkalt section=view}">
            <span class="icon icon-search-plus icon-lg left" role="presentation" aria-hidden="true" ></span>
            <span class="visually-hidden">{str tag=detailslinkalt section=view}</span>
        </button>
        {/if}

        {if $headertype == "outcomeoverview" && $showmanagebutton }
          <button data-url="{$managaoutcomesurl}" type="button" class="btn btn-secondary" title="{str tag=configureoutcomes section=collection}">
              <span class="icon icon-cogs icon-lg left" role="presentation" aria-hidden="true" ></span>
              <span class="visually-hidden">{str tag=detailslinkalt section=view}</span>
          </button>
        {/if}

        {if $editurl}{strip}
            <button data-url="{$editurl}" type="button" class="btn btn-secondary" title="{str tag=editthisview section=view}">
                <span class="icon icon-pencil-alt icon-lg left" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{str tag=editthisview section=view}</span>
            </button>
        {/strip}{/if}

        {if $mnethost}
        <button
            data-url="{$mnethost.url}" class="btn btn-secondary" title="{str tag=backto arg1=$mnethost.name}">
            <span class="icon icon-long-arrow-alt-right icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="visually-hidden">{str tag=backto arg1=$mnethost.name}</span>
        </button>
        {/if}

        <button type="button" class="btn btn-secondary dropdown-toggle" title="{str tag='moreoptions'}" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="icon icon-ellipsis-h icon-lg" role="presentation" aria-hidden="true"></span>
            <span class="visually-hidden">{str tag="moreoptions"}</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" role="menu">

            {if $copyurl}{strip}
                <li class="dropdown-item with-icon">
                {if $downloadurl}
                    <a id="downloadview-button" title="{str tag=copythisportfolio section=view}" href="{$downloadurl}">
                {else}
                    <a id="copyview-button{if $headertype == "progresscompletion"}-progress{/if}" title="{str tag=copythisportfolio section=view}" href="{$copyurl}">
                {/if}
                    <span class="icon icon-regular icon-clone left" role="presentation" aria-hidden="true"></span>
                    <span class="link-text">{str tag=copy section=mahara}</span>
                    </a>
                </li>
            {/strip}{/if}

            {* Configure links - lead to the Page/Collection configure forms *}
            {if $issubmission || $editurl}
                <li class="dropdown-item with-icon">
                    <a href="{$configureurl}" title="{str tag=configure}">
                        <span class="icon icon-cogs left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="configure" section="mahara"}</span>
                        <span class="visually-hidden">{str(tag=configurespecific arg1=$maintitle)|escape:html|safe}</span>
                    </a>
                </li>
            {/if}

            {if $usercaneditview || $userisowner || $actionsallowed}
                <li class="dropdown-item with-icon">
                    <a id="" title="{str tag=managesharing section=view}" href="{$accessurl}">
                        <span class="icon {if $viewlocked}icon-share-nodes{else}icon-share-nodes{/if} left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=managesharing section=view}</span>
                    </a>
                </li>
            {/if}

            <li class="dropdown-item with-icon">
                <a title="{str tag=print section=view}" id="print_link" href="#" onclick="window.print(); return false;">
                    <span class="icon icon-print left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=print section=view}</span>
                    <span class="visually-hidden">{str tag=print section=view}</span>
                </a>
            </li>

            {if $LOGGEDIN}
                {if !$userisowner}
                    {if !($headertype == "matrix" || $headertype == "progresscompletion" || $headertype == "outcomeoverview")}
                    <li class="dropdown-item with-icon">
                        <a id="toggle_watchlist_link" class="watchlist" href="">
                        {if $viewbeingwatched}
                            <span class="icon icon-regular icon-eye-slash left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=removefromwatchlist section=view}</span>
                            {else}
                            <span class="icon icon-regular icon-eye left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=addtowatchlist section=view}</span>
                        {/if}
                        </a>
                    </li>
                    {/if}
                    {if !($headertype == "matrix")}
                    <li class="dropdown-item with-icon">
                        {if !($headertype == "matrix" || $headertype == "progresscompletion" || $headertype == "outcomeoverview")}
                            {if $objector}
                                <span class="nolink">
                                    <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=objectionablematerialreported}</span>
                                </span>
                            {else}
                                <a id="objection_link" href="#" data-bs-toggle="modal" data-bs-target="#report-form">
                                    <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=reportobjectionablematerial}</span>
                                </a>
                            {/if}
                        {/if}
                    </li>
                    {/if}
                {/if}
                {if $undoverificationform}
                    <li class="dropdown-item with-icon">
                        <a id="undoverificationlink" href="#" data-bs-toggle="modal" data-bs-target="#undoverification-form">
                            <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=undoverification section=collection}</span>
                        </a>
                    </li>
                {/if}
                {if $revokeaccessform}
                    <li class="dropdown-item with-icon">
                        <a id="revokeaccesslink" href="#" data-bs-toggle="modal" data-bs-target="#revokemyaccess-form">
                            <span class="icon icon-trash-alt text-danger left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=removeaccess}</span>
                        </a>
                    </li>
                {/if}
                {if $userisowner && $objectedpage}
                <li class="dropdown-item with-icon">
                    <a id="review_link" href="#" data-bs-toggle="modal" data-bs-target="#review-form">
                        <span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=objectionreview}</span>
                    </a>
                </li>
                {/if}
                {if ($userisowner || $canremove) && !($headertype == "matrix" || $headertype == "progresscompletion" || $headertype == "outcomeoverview")}
                <li class="dropdown-item with-icon">
                    <a href="{$WWWROOT}view/delete.php?id={$viewid}" title="{str tag=deletethisview section=view}">
                        <span class="icon icon-trash-alt text-danger left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=deletethisview section=view}</span>
                        <span class="visually-hidden">{str(tag=deletespecific arg1=$maintitle)|escape:html|safe}</span>
                    </a>
                </li>
                {/if}
            {/if}
            {if $versionurl}
                <li class="dropdown-item with-icon">
                  <a href="{$versionurl}">
                      <span class="icon icon-history left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=timeline section=view}</span>
                      <span class="visually-hidden">{str(tag=timelinespecific section=view arg1=$maintitle)|escape:html|safe}</span>
                  </a>
                </li>
            {/if}
            {if $userisowner && !($headertype == "matrix" || $headertype == "progresscompletion" || $headertype == "outcomeoverview") }
                <li class="dropdown-item with-icon">
                  <a href="{$createversionurl}">
                      <span class="icon icon-save left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag=savetimeline section=view}</span>
                      <span class="visually-hidden">{str(tag=savetimelinespecific section=view arg1=$maintitle)|escape:html|safe}</span>
                  </a>
                </li>
            {/if}
            {if $LOGGEDIN}
                <li class="dropdown-item with-icon">
                    <a href="{$url}">
                        <span class="icon icon-layer-group left" aria-hidden="true" role="presentation"></span><span class="link-text">{$linktext}</span>
                    </a>
                </li>
            {/if}
        </ul>
    </div>
</div>
