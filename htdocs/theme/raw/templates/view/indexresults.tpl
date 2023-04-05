                {foreach from=$views item=view name=loopidx}
                <div class="card-quarter {if $view.collid}card-collection{else}card-view{/if}">
                    <div class="card {if $view.issubmission} bg-submitted{/if}{if $view.isreleased} bg-released{/if}
                    {if $view.template == $sitetemplate} site-template{/if}">
                        <h2 class="card-header has-link">
                            <a class="title-link title"
                            href="
                                {if $view.template == $sitetemplate}
                                    {$WWWROOT}view/blocks.php?id={$view.id}
                                {elseif isset($outcomesgroup) && $outcomesgroup && $view.collid && $view.outcomeportfolio}
                                    {$WWWROOT}collection/outcomesoverview.php?id={$view.collid}
                                {elseif isset($view.numviews) && $view.numviews == 0}
                                    {$WWWROOT}collection/views.php?id={$view.collid}
                                {else}
                                    {$view.fullurl}
                                {/if}"
                            title="{$view.displaytitle}">
                                {$view.displaytitle}
                            </a>
                        </h2>
                        <div class="card-body{if $view.coverimage} coverimage{/if}">
                            {if $view.coverimage && $view.coverimageurl}
                            <div class="widget-heading">
                                <img src="{$view.coverimageurl}"
                                {if $view.coverimagedescription}
                                    alt="{$view.coverimagedescription|str_shorten_html:120:true|strip_tags|safe}"
                                {/if}
                                width="100%">
                            </div>
                            {if $view.type == 'profile'}
                            <div class="widget-detail"><p>{str tag=profiledescription}</p></div>
                            {elseif $view.type == 'dashboard'}
                            <div class="widget-detail"><p>{str tag=dashboarddescription1}</p></div>
                            {elseif $view.type == 'grouphomepage'}
                            <div class="widget-detail"><p>{str tag=grouphomepagedescription section=view}</p></div>
                            {elseif $view.description}
                            <div class="widget-detail">
                                <p>
                                {if $view.issitetemplate && $view.type == 'portfolio'}
                                   {$view.description|str_shorten_html:160:true|strip_tags|safe}
                                {else}
                                   {$view.description|str_shorten_html:120:true|strip_tags|safe}
                                {/if}
                                </p>
                            </div>
                            {/if}
                            {else}
                            <div class="detail text-small">
                                {if $view.type == 'profile'}
                                    <p>{str tag=profiledescription}</p>
                                {elseif $view.type == 'dashboard'}
                                    <p>{str tag=dashboarddescription1}</p>
                                {elseif $view.type == 'grouphomepage'}
                                    <p>{str tag=grouphomepagedescription section=view}</p>
                                {elseif $view.description}
                                    <p>
                                    {if $view.issitetemplate && $view.type == 'portfolio'}
                                       {$view.description|str_shorten_html:160:true|strip_tags|safe}
                                    {else}
                                       {$view.description|str_shorten_html:120:true|strip_tags|safe}
                                    {/if}
                                  </p>
                                {else}
                                &nbsp;
                                {/if}
                            </div>
                            {/if}
                        </div>
                        <div class="card-footer">
                            {* Note: This is positioned relative to base of card-quarter *}
                            {if !($outcomesgroup && $role === 'member')}
                            <div class="page-access">
                                {if $view.accesslist || $view.manageaccess}
                                    <button class="btn btn-link dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">                                        <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                                        <span class="icon {if !$view.accesslist}icon-lock{else}icon-unlock{/if} close-indicator" role="presentation" aria-hidden="true"></span>
                                        <span class="visually-hidden">{str tag="sharingrulesfor" section="view" arg1="$view.vtitle"}</span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                      {if $view.manageaccesssuspended}
                                        <li class="view-details">{str tag="pending" section="view"}</li>
                                      {else}
                                        {foreach from=$view.manageaccess item=manageitem}
                                        <li class="dropdown-item with-icon">
                                            {if $manageitem->accesstype == 'managesharing'}
                                            <a class="seperator" href="{$WWWROOT}view/accessurl.php?return=index&id={$view.id}{if $view.collid}&collection={$view.collid}{/if}">
                                                <span class="icon {if $view.locked}icon-lock{else}icon-share-nodes{/if} left" role="presentation" aria-hidden="true"></span><span class="link-text">{$manageitem->displayname}</span>
                                                <span class="visually-hidden">{$manageitem->accessibilityname}</span>
                                            </a>
                                            {/if}
                                        </li>
                                        {/foreach}
                                        {foreach from=$view.accesslist item=accessitem}
                                        <li class="dropdown-item with-icon">
                                            <a href="{$WWWROOT}view/accessurl.php?id={$view.id}{if $view.collid}&collection={$view.collid}{/if}">
                                            {if $accessitem->accesstype == 'loggedin'}
                                                <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="registeredusers" section="view"}</span>
                                            {elseif $accessitem->accesstype == 'public'}
                                                <span class="icon icon-globe left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="public" section="view"}</span>
                                            {elseif $accessitem->accesstype == 'friends'}
                                                <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="friends" section="view"}</span>
                                            {elseif $accessitem->group}
                                                <span class="icon icon-people-group left" role="presentation" aria-hidden="true"></span><span class="link-text">{$accessitem->displayname}</span>
                                            {elseif $accessitem->institution}
                                                <span class="icon icon-university left" role="presentation" aria-hidden="true"></span><span class="link-text">{$accessitem->displayname}</span>
                                            {elseif $accessitem->usr}
                                                <span class="icon icon-user left" role="presentation" aria-hidden="true"></span><span class="link-text">{$accessitem->displayname}{if $accessitem->role} ({$accessitem->roledisplay}){/if} </span>
                                            {elseif $accessitem->token}
                                                <span class="icon icon-globe left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="token" section="view"}</span>
                                            {/if}
                                            </a>
                                        </li>
                                        {/foreach}
                                      {/if}
                                    </ul>
                                {/if}
                            </div>
                            {/if}
                            <div class="page-controls">
                                {if $view.submittedto}
                                <button class="dropdown-toggle btn btn-link moremenu" type="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{str tag='submittedinfo' section='mahara'}">
                                    <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="icon icon-check-to-slot close-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="visually-hidden">{str tag=submittedinfofor section=mahara arg1="$view.vtitle"}</span>
                                </button>
                                {elseif $view.issubmission}
                                <button class="dropdown-toggle btn btn-link moremenu" type="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{str tag='releasedinfo' section='mahara'}">
                                    <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="icon icon-square-check close-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="visually-hidden">{str tag=releasedinfofor section=mahara arg1="$view.vtitle"}</span>
                                </button>
                                {else}
                                <button class="dropdown-toggle btn btn-link moremenu" type="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{str tag='moreoptions' section='mahara'}">
                                    <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="icon icon-ellipsis-v close-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="visually-hidden">{str tag=moreoptionsfor section=mahara arg1="$view.vtitle"}</span>
                                </button>
                                {/if}
                                <ul class="dropdown-menu dropdown-menu-end" role="menu">
                                {if $view.collid && !$view.submittedto && !$noedit && !$view.lockedcoll && !$view.issubmission}
                                    <li class="dropdown-item with-icon">
                                    {if $outcomesgroup && $view.collection->get('outcomeportfolio')}
                                      {if $role === 'admin'}
                                      <a href="{$WWWROOT}collection/manageoutcomes.php?id={$view.collid}" title="{str tag=manageoutcomes section=collection}" >
                                        <span class="icon icon-list left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="manage" section="collection"}</span>
                                        <span class="visually-hidden">{str(tag=manageoutcomesspecific section=collection arg1=$view.displaytitle)|escape:html|safe}</span>
                                      </a>
                                      {/if}
                                    {else}
                                        <a href="{$WWWROOT}collection/views.php?id={$view.collid}" title="{str tag=manageviews section=collection}">
                                            <span class="icon icon-list left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="manage" section="collection"}</span>
                                            <span class="visually-hidden">{str(tag=manageviewsspecific section=collection arg1=$view.displaytitle)|escape:html|safe}</span>
                                        </a>
                                    {/if}
                                    </li>
                                {/if}

                                {* Edit link - leads to the Page edit screen *}
                                {if !$view.submittedto && !$noedit && (!$view.locked || $editlocked) && !$view.lockedcoll && !$view.issubmission && !$view.collid}
                                    <li class="dropdown-item with-icon">
                                        <a href="{$WWWROOT}view/blocks.php?id={$view.id}&{$querystring}" title="{str tag ="editcontentandlayout" section="view"}">
                                            <span class="icon icon-pencil-alt left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="edit" section="mahara"}</span>
                                            <span class="visually-hidden">{str(tag=editspecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                        </a>
                                    </li>
                                {/if}

                                {* Configure link - leads to the Page/Collection configure forms *}
                                {if !$view.submittedto && !$noedit && (!$view.locked || $editlocked) && !$view.lockedcoll && !($view.collid && $outcomesgroup && $role !== 'admin')}
                                    <li class="dropdown-item with-icon">
                                    {if $view.collid}
                                        <a href="{$WWWROOT}collection/edit.php?id={$view.collid}" title="{str tag=edittitleanddescription section=view}">
                                    {else}
                                        <a href="{$WWWROOT}view/editlayout.php?id={$view.id}&{$querystring}" title="{str tag ="editcontentandlayout" section="view"}">
                                    {/if}
                                            <span class="icon icon-cogs left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="configure" section="mahara"}</span>
                                            <span class="visually-hidden">{str(tag=configurespecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                        </a>
                                    </li>
                                {/if}

                                {if !$view.submittedto && $view.removable && !$noedit && (!$view.locked || $editlocked) && !$view.lockedcoll && !$view.issubmission && !($outcomesgroup && $role === 'member')}
                                    <li class="dropdown-item with-icon">
                                    {if $view.collid}
                                        <a href="{$WWWROOT}collection/delete.php?id={$view.collid}" title="{str tag=deletecollection section=collection}">
                                    {else}
                                        <a href="{$WWWROOT}view/delete.php?id={$view.id}&{$querystring}" title="{str tag=deletethisview section=view}">
                                    {/if}
                                             <span class="icon icon-trash-alt text-danger left" role="presentation" aria-hidden="true"></span><span class="link-text">{str tag="delete" section="mahara"}</span>
                                             <span class="visually-hidden">{str(tag=deletespecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                        </a>
                                    </li>
                                {/if}
                                <li class="view-details dropdown-item">
                                    {str tag=Created section=mahara} {format_date(strtotime($view.vctime), 'strftimerecentyear')}
                                    <br>
                                    {str tag=modified section=mahara} {format_date(strtotime($view.vmtime), 'strftimerecentyear')}
                                    <br>
                                    {if $view.lockedcoll && $view.unlockcoll}{str tag=lockedcollection section=collection arg1=$view.unlockcoll}
                                    <br>
                                    {/if}
                                </li>
                                {if $view.submittedto}
                                <li class="view-details dropdown-item">
                                    {$view.submittedto|clean_html|safe}
                                </li>
                                {/if}
                                {if $view.manageaccesssuspended}
                                <li class="view-details dropdown-item">
                                    {str tag=pending section=view}
                                </li>
                                {/if}
                                </ul>{* hamburger buttons *}
                            </div>

                            {* Are we a submission? *}
                            {if $view.submissionoriginalurl}
                                <div class="page-controls" title="{str tag='viewsourceportfolio' section='view' arg1=$view.submissionoriginaltitle}">
                                    <a href="{$view.submissionoriginalurl}" class="btn btn-link"><span class="icon icon-turn-up"></span></a>
                                </div>
                            {/if}
                            {* Are we a submission that has had its origin deleted? *}
                            {if $view.issubmissionbutoriginaldeleted}
                                <div class="page-controls" title="{str tag='originalsubmissiondeleted' section='view'}">
                                    <div class="btn">
                                        <span class="icon icon-turn-up"></span>
                                    </div>
                                </div>
                            {/if}

                            {if $view.collid}
                                {assign var=fullnumviews value=$view.numviews}
                                {if $view.progresscompletion}
                                    {assign var=fullnumviews value=$fullnumviews + 1}
                                {/if}
                                {if $view.framework}
                                    {assign var=fullnumviews value=$fullnumviews + 1}
                                {/if}
                                {if $view.outcomes}
                                    {assign var=fullnumviews value=$fullnumviews + 1}
                                {/if}
                                <div class="collection-list" title="{str tag='numviewsincollection' section='collection' arg1='$fullnumviews'}">
                                    {if $view.numviews > 0}
                                    <a href="#" class="dropdown-toggle btn btn-link" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                                        <span class="page-count">{$fullnumviews}</span>
                                        <span class="icon icon-file close-indicator" role="presentation" aria-hidden="true">
                                            {if $view.numviews > 1}
                                            <span class="collection-indicator few"></span>
                                                {if $view.numviews > 10}
                                                <span class="collection-indicator many"></span>
                                                {/if}
                                            {/if}
                                        </span>
                                    </a>
                                    {else}
                                    <a href="#" class="dropdown-toggle btn btn-link">
                                        <span class="page-count">{$view.numviews}</span>
                                        <span class="icon icon-file" role="presentation" aria-hidden="true">
                                    </a>
                                    {/if}
                                    {if $view.collid && $view.collviews > 0}
                                    <ul class="dropdown-menu" role="menu">
                                        {if $view.outcomes}
                                        <li class="dropdown-item with-icon">
                                            <a href="{$view.outcomes->fullurl}">
                                                <span class="icon icon-clipboard-check left" role="presentation" aria-hidden="true"></span><span class="link-text">{$view.outcomes->title}</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {if $view.progresscompletion}
                                        <li class="dropdown-item with-icon">
                                            <a href="{$view.progresscompletion->fullurl}">
                                                <span class="icon icon-clipboard-check left" role="presentation" aria-hidden="true"></span><span class="link-text">{$view.progresscompletion->title}</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {if $view.framework}
                                        <li class="dropdown-item with-icon">
                                            <a href="{$view.framework->fullurl}">
                                                <span class="icon icon-clipboard-check left" role="presentation" aria-hidden="true"></span><span class="link-text">{$view.framework->title}</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {foreach from=$view.collviews.views item=cview}
                                        <li class="dropdown-item with-icon">
                                            <a href="{$cview->fullurl}">
                                                <span class="icon icon-regular icon-file left" role="presentation" aria-hidden="true"></span><span class="link-text">{$cview->title}</span>
                                            </a>
                                        </li>
                                        {/foreach}
                                    </ul>
                                    {/if}
                                </div>
                            {/if}

                        </div>
                    </div>
                    {if $view.collid}
                        <div class="collection-stack {if $view.submittedto} bg-warning{/if}"></div>
                    {/if}
                </div>
                {/foreach}
                {$pagination|safe}
