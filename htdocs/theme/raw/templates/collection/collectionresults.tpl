        {foreach from=$collections item=collection}
            <div class="list-group-item collection-item {if $collection->submitinfo}list-group-item-warning{/if}">
                {if $collection->views[0]->view}
                   <a href="{if $collection->frameworkname}{$collection->fullurl}{else}{$collection->views[0]->fullurl}{/if}" class="outer-link"><span class="sr-only">{$collection->name}</span></a>
                {/if}
                 <div class="row">
                    <div class="col-lg-9">

                        <h2 class="title list-group-item-heading" title="{str tag=emptycollection section=collection}">
                            {$collection->name}
                        </h2>
                        <div class="detail">{$collection->description}</div>

                        <div class="detail">
                            <span class="lead text-small">{str tag=Views section=view}:</span>
                            {if $collection->views}
                                {if $collection->frameworkname}
                                    <a href="{$collection->fullurl}">{$collection->frameworkname}</a>,
                                {/if}
                                {foreach from=$collection->views item=view name=cviews}
                                    <a href="{$view->fullurl}" class="inner-link">{$view->title}</a>{if !$.foreach.cviews.last}, {/if}
                                {/foreach}
                            {else}
                                {str tag=none}
                            {/if}
                        </div>

                        {if $collection->submitinfo}
                            <div class="detail submitted-viewitem">{str tag=collectionsubmittedtogroupon section=view arg1=$collection->submitinfo->url arg2=$collection->submitinfo->name arg3=$collection->submitinfo->time|format_date}</div>
                        {/if}
                    </div>
                     <div class="col-md-3">
                        <div class="inner-link btn-action-list">
                            {if !$collection->submitinfo && $canedit}
                                <div class="btn-top-right btn-group btn-group-top">
                                    <a href="{$WWWROOT}collection/views.php?id={$collection->id}" title="{str tag=manageviews section=collection}" class="btn btn-secondary btn-sm">
                                        <span class="icon icon-list icon-lg text-default" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str(tag=manageviewsspecific section=collection arg1=$collection->name)|escape:html|safe}</span>
                                    </a>
                                    <a href="{$WWWROOT}collection/edit.php?id={$collection->id}" title="{str tag=edittitleanddescription section=view}" class="btn btn-secondary btn-sm">
                                        <span class="icon icon-pencil icon-lg text-default" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str(tag=editspecific arg1=$collection->name)|escape:html|safe}</span>
                                    </a>
                                    <a href="{$WWWROOT}collection/delete.php?id={$collection->id}" title="{str tag=deletecollection section=collection}" class="btn btn-secondary btn-sm">
                                        <span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str(tag=deletespecific arg1=$collection->name)|escape:html|safe}</span>
                                    </a>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
