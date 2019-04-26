                {foreach from=$views item=view name=loopidx}
                <div class="card-quarter {if $view.collid}card-collection{else}card-view{/if}">
                    <div class="card {if $view.submittedto} bg-submitted{/if}
                    {if $view.template == $sitetemplate} site-template{/if}">
                        <h3 class="card-header has-link">
                            <a class="title-link title"
                            href="
                                {if $view.template == $sitetemplate}
                                    {$WWWROOT}view/blocks.php?id={$view.id}
                                {elseif isset($view.numviews) && $view.numviews == 0}
                                    {$WWWROOT}collection/views.php?id={$view.collid}
                                {else}
                                    {$view.fullurl}
                                {/if}"
                            title="{$view.displaytitle}">
                                {$view.displaytitle}
                            </a>
                        </h3>
                        <div class="card-body">
                            <div class="detail">
                                {if $view.type == 'profile'}
                                    <div class="detail">{str tag=profiledescription}</div>
                                {elseif $view.type == 'dashboard'}
                                    <div class="detail">{str tag=dashboarddescription}</div>
                                {elseif $view.type == 'grouphomepage'}
                                    <div class="detail">{str tag=grouphomepagedescription section=view}</div>
                                {elseif $view.description}
                                    <div class="detail">
                                    {if $view.issitetemplate && $view.type == 'portfolio'}
                                       {$view.description|str_shorten_html:160:true|strip_tags|safe}
                                    {else}
                                       {$view.description|str_shorten_html:120:true|strip_tags|safe}
                                    {/if}
                                    </div>
                                {else}
                                &nbsp;
                                {/if}
                            </div>
                        </div>
                        <div class="card-footer">
                            {* Note: This is positioned relative to base of card-quarter *}
                            <div class="page-access">
                                {if $view.accesslist || $view.manageaccess}
                                    <a href="#" class="dropdown-toggle btn btn-link" data-toggle="dropdown" aria-expanded="false" title="{str tag='manageaccess' section='view'}">
                                        <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                                        <span class="icon {if !$view.accesslist}icon-lock{else}icon-unlock-alt{/if} close-indicator" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag="accessrulesfor" section="view" arg1="$view.vtitle"}</span>
                                    </a>
                                    <ul class="dropdown-menu" role="menu">
                                      {if $view.manageaccesssuspended}
                                        <li class="view-details">{str tag="pending" section="view"}</li>
                                      {else}
                                        {foreach from=$view.manageaccess item=manageitem}
                                        <li class="dropdown-item">
                                            {if $manageitem->accesstype == 'managesharing'}
                                            <a class="seperator" href="{$WWWROOT}view/accessurl.php?id={$view.id}{if $view.collid}&collection={$view.collid}{/if}">
                                                <span class="icon {if $view.locked}icon-lock{else}icon-unlock-alt{/if} left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$manageitem->displayname}</span>
                                                <span class="sr-only">{$manageitem->accessibilityname}</span>
                                            </a>
                                            {/if}
                                        </li>
                                        {/foreach}
                                        {foreach from=$view.accesslist item=accessitem}
                                        <li class="dropdown-item">
                                            <a href="{$WWWROOT}view/accessurl.php?id={$view.id}{if $view.collid}&collection={$view.collid}{/if}">
                                            {if $accessitem->accesstype == 'loggedin'}
                                                <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{str tag="registeredusers" section="view"}</span>
                                            {elseif $accessitem->accesstype == 'public'}
                                                <span class="icon icon-globe left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{str tag="public" section="view"}</span>
                                            {elseif $accessitem->accesstype == 'friends'}
                                                <span class="icon icon-user-plus left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{str tag="friends" section="view"}</span>
                                            {elseif $accessitem->group}
                                                <span class="icon icon-users left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$accessitem->displayname}</span>
                                            {elseif $accessitem->institution}
                                                <span class="icon icon-university left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$accessitem->displayname}</span>
                                            {elseif $accessitem->usr}
                                                <span class="icon icon-user left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$accessitem->displayname}{if $accessitem->role} ({$accessitem->roledisplay}){/if} </span>
                                            {elseif $accessitem->token}
                                                <span class="icon icon-globe left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{str tag="token" section="view"}</span>
                                            {/if}
                                            </a>
                                        </li>
                                        {/foreach}
                                      {/if}
                                    </ul>
                                {/if}
                            </div>

                            <div class="page-controls">
                                <a href="#" class="dropdown-toggle moremenu btn btn-link" data-toggle="dropdown" aria-expanded="false" title="{str tag='moreoptions' section='mahara'}">
                                    <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="icon icon-ellipsis-v close-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="sr-only">{str tag=moreoptionsfor section=mahara arg1="$view.vtitle"}</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                {if $view.collid && !$view.submittedto && !$noedit}
                                    <li class="dropdown-item">
                                        <a href="{$WWWROOT}collection/views.php?id={$view.collid}" title="{str tag=manageviews section=collection}">
                                            <span class="icon icon-list left" role="presentation" aria-hidden="true"></span>
                                            <span class="link-text">{str tag="manage" section="collection"}</span>
                                            <span class="sr-only">{str(tag=manageviewsspecific section=collection arg1=$view.displaytitle)|escape:html|safe}</span>
                                        </a>
                                    </li>
                                {/if}
                                {if !$view.submittedto && !$noedit && (!$view.locked || $editlocked)}
                                    <li class="dropdown-item">
                                    {if $view.collid}
                                        <a href="{$WWWROOT}collection/edit.php?id={$view.collid}" title="{str tag=edittitleanddescription section=view}">
                                    {else}
                                        <a href="{$WWWROOT}view/blocks.php?id={$view.id}&{$querystring}" title="{str tag ="editcontentandlayout" section="view"}">
                                    {/if}
                                            <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
                                            <span class="link-text">{str tag="edit" section="mahara"}</span>
                                            <span class="sr-only">{str(tag=editspecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                        </a>
                                    </li>
                                {/if}
                                {if !$view.submittedto && $view.removable && !$noedit && (!$view.locked || $editlocked)}
                                    <li class="dropdown-item">
                                    {if $view.collid}
                                        <a href="{$WWWROOT}collection/delete.php?id={$view.collid}" title="{str tag=deletecollection section=collection}">
                                    {else}
                                        <a href="{$WWWROOT}view/delete.php?id={$view.id}&{$querystring}" title="{str tag=deletethisview section=view}">
                                    {/if}
                                             <span class="icon icon-trash text-danger left" role="presentation" aria-hidden="true"></span>
                                             <span class="link-text">{str tag="delete" section="mahara"}</span>
                                             <span class="sr-only">{str(tag=deletespecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                        </a>
                                    </li>
                                {/if}
                                <li class="view-details dropdown-item">
                                    {str tag=Created section=mahara} {format_date(strtotime($view.vctime), 'strftimerecentyear')}
                                    <br>
                                    {str tag=modified section=mahara} {format_date(strtotime($view.vmtime), 'strftimerecentyear')}
                                    <br>
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

                            {if $view.collid}
                                {assign var=fullnumviews value=$view.numviews}
                                {if $view.framework}
                                    {assign var=fullnumviews value=$view.numviews + 1}
                                {/if}
                                <div class="collection-list" title="{str tag='numviewsincollection' section='collection' arg1='$fullnumviews'}">
                                    {if $view.numviews > 0}
                                    <a href="#" class="dropdown-toggle btn btn-link" data-toggle="dropdown" aria-expanded="false">
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
                                        {if $view.framework}
                                        <li class="dropdown-item">
                                            <a href="{$view.framework->fullurl}">
                                                <span class="icon icon-clipboard left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$view.framework->title}</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {foreach from=$view.collviews.views item=cview}
                                        <li class="dropdown-item">
                                            <a href="{$cview->fullurl}">
                                                <span class="icon icon-file-o left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$cview->title}</span>
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
