                {foreach from=$views item=view}
                    <div class="list-group-item {if $view.submittedto} list-group-item-warning {/if}">
                        {if !$view.issitetemplate}
                            <a href="{$view.fullurl}" class="outer-link"><span class="sr-only">{$view.displaytitle}</span></a>
                        {/if}
                        <div class="row">
                            <div class="col-md-9">
                                <h3 class="title list-group-item-heading">
                                    {$view.displaytitle}
                                </h3>

                                {if $view.submittedto}
                                    <div class="detail submitted-viewitem">{$view.submittedto|clean_html|safe}</div>
                                {elseif $view.type == 'profile'}
                                    <div class="detail">{str tag=profiledescription}</div>
                                {elseif $view.type == 'dashboard'}
                                    <div class="detail">{str tag=dashboarddescription}</div>
                                {elseif $view.type == 'grouphomepage'}
                                    <div class="detail">{str tag=grouphomepagedescription section=view}</div>
                                {elseif $view.description}
                                    <div class="detail">
                                    {if $view.issitetemplate && $view.type == 'portfolio'}
                                       {$view.description|strip_tags|safe}
                                    {else}
                                       {$view.description|str_shorten_html:110:true|strip_tags|safe}
                                    {/if}
                                    </div>
                                {/if}
                            </div>
                            <div class="col-md-3">
                                <div class="inner-link btn-action-list">
                                    <div class="btn-top-right btn-group btn-group-top">
                                        {if !$view.submittedto && (!$view.locked || $editlocked)}
                                            <a href="{$WWWROOT}view/blocks.php?id={$view.id}&{$querystring}" title="{str tag ="editcontentandlayout" section="view"}" class="btn btn-default btn-xs">
                                                <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                                                <span class="sr-only">{str(tag=editspecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                            </a>
                                        {/if}
                                        {if !$view.submittedto && $view.removable && (!$view.locked || $editlocked)}
                                            <a href="{$WWWROOT}view/delete.php?id={$view.id}&{$querystring}" title="{str tag=deletethisview section=view}" class="btn btn-default btn-xs">
                                                <span class="icon icon-lg icon-trash text-danger" role="presentation" aria-hidden="true"></span>
                                                <span class="sr-only">{str(tag=deletespecific arg1=$view.displaytitle)|escape:html|safe}</span>
                                            </a>
                                        {/if}
                                    </div>
                                </div>{* rbuttons *}
                            </div>
                        </div>
                    </div>
                {/foreach}
