{include file="header.tpl"}
{if $GROUP}
    <h2>{$PAGESUBHEADING}</h2>
{/if}
{if $canedit}
    <div class="rbuttons {if $GROUP}pagetabs{/if}">
        <a class="btn" href="{$WWWROOT}collection/edit.php?new=1{$urlparamsstr}">{str section=collection tag=newcollection}</a>
        <a class="btn" href="{$WWWROOT}view/choosetemplate.php?searchcollection=1{$urlparamsstr}">{str section=collection tag=copyacollection}</a>
    </div>
{/if}
{if $institution}{$institutionselector|safe}{/if}
<p class="intro">{str tag=collectiondescription section=collection}</p>
{if !$canedit}<p>{str tag=canteditgroupcollections section=collection}</p>{/if}
{if $collections}
    <div id="mycollections" class="fullwidth listing">
        {foreach from=$collections item=collection}
            <div class="listrow {cycle values='r0,r1'}">
                {if $collection->views[0]->view}
                    <h3 class="title"><a href="{$collection->views[0]->fullurl}">{$collection->name}</a></h3>
                {else}
                    <h3 class="title" title="{str tag=emptycollection section=collection}">{$collection->name}</h3>
                {/if}

                {if !$collection->submitinfo && $canedit}
                    <div class="fr viewcontrolbuttons">
                        <a href="{$WWWROOT}collection/views.php?id={$collection->id}" title="{str tag=manageviews section=collection}">
                            <img src="{theme_image_url filename='btn_configure'}" alt="{str(tag=manageviewsspecific section=collection arg1=$collection->name)|escape:html|safe}">
                        </a>
                        <a href="{$WWWROOT}collection/edit.php?id={$collection->id}" title="{str tag=edittitleanddescription section=view}">
                            <img src="{theme_image_url filename='btn_edit'}" alt="{str(tag=editspecific arg1=$collection->name)|escape:html|safe}">
                        </a>
                        <a href="{$WWWROOT}collection/delete.php?id={$collection->id}" title="{str tag=deletecollection section=collection}">
                            <img src="{theme_image_url filename='btn_deleteremove'}" alt="{str(tag=deletespecific arg1=$collection->name)|escape:html|safe}">
                        </a>
                    </div>
                {/if}


                <div class="detail">{$collection->description}</div>

                <div class="detail">
                    <strong>{str tag=Views section=view}:</strong>
                    {if $collection->views}
                        {foreach from=$collection->views item=view name=cviews}
                            <a href="{$view->fullurl}">{$view->title}</a>{if !$.foreach.cviews.last}, {/if}
                        {/foreach}
                    {else}
                        {str tag=none}
                    {/if}
                </div>

                {if $collection->submitinfo}
                    <div class="detail submitted-viewitem">{str tag=collectionsubmittedtogroupon section=view arg1=$collection->submitinfo->url arg2=$collection->submitinfo->name arg3=$collection->submitinfo->time|format_date}</div>
                {/if}
                <div class="cb"></div>
            </div>
        {/foreach}
    </div>
       {$pagination|safe}
{else}
        <div class="message">{str tag=nocollections section=collection}{if $addonelink} <a href={$addonelink}>{str tag=addone}</a>{/if}</div>
{/if}
{include file="footer.tpl"}
