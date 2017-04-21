                {foreach from=$views item=view name=loopidx}
                    <div class="panel panel-default panel-quarter {if $view.submittedto} panel-warning {/if}
                    {if $.foreach.loopidx.index % 4 == 0} firstquarter{/if}
                    {if ($.foreach.loopidx.index +1) % 4 == 0} lastquarter{/if}
                    {if $.foreach.loopidx.index % 2 == 0} firsthalf{/if}
                    {if ($.foreach.loopidx.index +1) % 2 == 0} lasthalf{/if}
                    {if $view.collid}panel-collection{else}panel-view{/if}
                    {if $view.template == $sitetemplate} site-template{/if}">
                    {if $view.collid}<div class="panel panel-default panel-stack{if $view.submittedto} panel-warning{/if}"><div class="panel panel-default panel-stack{if $view.submittedto} panel-warning{/if}">{/if}
                        <h3 class="panel-heading has-link">
                            <a class="title-link title" href="{if $view.template == $sitetemplate}{$WWWROOT}view/blocks.php?id={$view.id}{elseif $view.numviews > 0}{$view.fullurl}{else}{$WWWROOT}collection/views.php?id={$view.collid}{/if}" title="{$view.displaytitle}">
                                {$view.displaytitle}
                            </a>
                        </h3>
                        <div class="panel-body">
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
                        <div class="panel-footer">
                            {* Note: This is positioned relative to base of panel-quarter *}
                            <div class="elipsis-right">
                                <button class="dropdown-toggle moremenu" type="button" data-toggle="dropdown" aria-expanded="false" title="{str tag='more...' section='mahara'}">
                                    <span class="icon icon-ellipsis-v link-indicator" role="presentation" aria-hidden="true"></span>
                                    <span class="sr-only">{str tag=moreoptionsfor section=mahara arg1="$view.vtitle"}</span>
                                </button>
                                <span class="collnum-arrow icon icon-chevron-down"></span>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                {if $view.collid && !$view.submittedto && !$noedit}
                                    <li>
                                        <a href="{$WWWROOT}collection/views.php?id={$view.collid}" title="{str tag=manageviews section=collection}">
                                            <span class="icon icon-list left" role="presentation" aria-hidden="true"></span>
                                            <span class="sr-only">{str(tag=manageviewsspecific section=collection arg1=$view.displaytitle)|escape:html|safe}</span>
                                            {str tag="manage" section="collection"}
                                        </a>
                                    </li>
                                {/if}
                                {if !$view.submittedto && !$noedit && (!$view.locked || $editlocked)}
                                    <li>
                                    {if $view.collid}
                                        <a href="{$WWWROOT}collection/edit.php?id={$view.collid}" title="{str tag=edittitleanddescription section=view}">
                                    {else}
                                        <a href="{$WWWROOT}view/blocks.php?id={$view.id}&{$querystring}" title="{str tag ="editcontentandlayout" section="view"}">
                                    {/if}
                                            <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
                                            <span class="sr-only">{str(tag=editspecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                            {str tag="edit" section="mahara"}
                                        </a>
                                    </li>
                                {/if}
                                {if !$view.submittedto && $view.removable && !$noedit && (!$view.locked || $editlocked)}
                                    <li>
                                    {if $view.collid}
                                        <a href="{$WWWROOT}collection/delete.php?id={$view.collid}" title="{str tag=deletecollection section=collection}">
                                    {else}
                                        <a href="{$WWWROOT}view/delete.php?id={$view.id}&{$querystring}" title="{str tag=deletethisview section=view}">
                                    {/if}
                                             <span class="icon icon-trash text-danger left" role="presentation" aria-hidden="true"></span>
                                             <span class="sr-only">{str(tag=deletespecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                             {str tag="delete" section="mahara"}
                                        </a>
                                    </li>
                                {/if}
                                <li class="view-details">
                                    <span>{str tag=Created section=mahara} {format_date(strtotime($view.vctime), 'strftimerecentyear')}</span>
                                    <span>{str tag=modified section=mahara} {format_date(strtotime($view.vmtime), 'strftimerecentyear')}</span>
                                    {if $view.submittedto}
                                        <span>{$view.submittedto|clean_html|safe}</span>
                                    {/if}
                                </li>
                                </ul>{* hamburger buttons *}
                            </div>

                            <div class="dropdown-toggle access-list">
                                {if $view.accesslist || $view.manageaccess}
                                    <a href="#" data-toggle="dropdown" aria-expanded="false" title="{str tag='manageaccess' section='view'}">
                                        <span class="icon {if !$view.accesslist}icon-lock{else}icon-unlock-alt{/if}"></span>
                                        <span class="collnum-arrow icon icon-chevron-down"></span>
                                        <span class="sr-only">{str tag="accessrulesfor" section="view" arg1="$view.vtitle"}</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                        {foreach from=$view.manageaccess item=manageitem}
                                        <li>
                                            {if $manageitem->accesstype == 'managesharing'}
                                            <a href="{$WWWROOT}view/access.php?id={$view.id}{if $view.collid}&collection={$view.collid}{/if}">
                                                <span class="icon {if $view.locked}icon-lock{else}icon-unlock-alt{/if} left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$manageitem->displayname}</span>
                                                <span class="sr-only">{$manageitem->accessibilityname}</span>
                                            </a>
                                            {elseif $manageitem->accesstype == 'managekeys'}
                                            <a class="seperator" href="{$WWWROOT}view/urls.php?id={$view.id}{if $view.collid}&collection={$view.collid}{/if}">
                                                <span class="icon icon-key left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$manageitem->displayname}</span>
                                                <span class="sr-only">{$manageitem->accessibilityname}</span>
                                            </a>
                                            {/if}
                                        </li>
                                        {/foreach}
                                        {foreach from=$view.accesslist item=accessitem}
                                        <li>
                                            <a href="{$WWWROOT}view/{if $accessitem->token}urls.php{else}access.php{/if}?id={$view.id}{if $view.collid}&collection={$view.collid}{/if}">
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
                                                <span class="link-text">{$accessitem->displayname}</span>
                                            {elseif $accessitem->token}
                                                <span class="icon icon-globe left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{str tag="token" section="view"}</span>
                                            {/if}
                                            </a>
                                        </li>
                                        {/foreach}
                                    </ul>
                                {/if}
                            </div>

                            {if $view.collid}
                                <div class="dropdown-toggle collection-list list-{if $view.numviews lte 1}small{elseif $view.numviews lte 10}medium{else}large{/if}" title="{str tag='numviewsincollection' section='collection' arg1='$view.numviews'}" title="{str tag='numviewsincollection' section='collection' arg1='$view.numviews'}">
                                    {if $view.numviews > 0}
                                    <a href="#" data-toggle="dropdown" aria-expanded="false">
                                        <span class="collnum">{if $view.framework}
                                                                {$view.numviews + 1}
                                                              {else}
                                                                {$view.numviews}
                                                              {/if}</span>
                                        <span class="collnum-arrow icon icon-chevron-down"></span>
                                        <span class="icon icon-file">{if $view.numviews > 1}<span class="icon-stack">{if $view.numviews > 10}<span class="icon-stack"></span>{/if}</span>{/if}</span>
                                    </a>
                                    {else}
                                        <span class="collnum">{$view.numviews}</span>
                                        <span class="icon icon-file">
                                    {/if}
                                    {if $view.collid && $view.collviews > 0}
                                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                        {if $view.framework}
                                        <li>
                                            <a href="{$view.framework->fullurl}">
                                                <span class="icon icon-clipboard left" role="presentation" aria-hidden="true"></span>
                                                <span class="link-text">{$view.framework->title}</span>
                                            </a>
                                        </li>
                                        {/if}
                                        {foreach from=$view.collviews.views item=cview}
                                        <li>
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
                    {if $view.collid}</div></div>{/if}
                    </div>
                {/foreach}
                {$pagination|safe}
