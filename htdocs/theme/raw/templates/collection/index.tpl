{include file="header.tpl"}
{if $GROUP}
    <h2>{$PAGESUBHEADING}</h2>
{/if}
{if $institution}{$institutionselector|safe}{/if}
{if $canedit}
    <div class="rbuttons {if $GROUP}pagetabs{/if}">
        <a class="btn" href="{$WWWROOT}collection/edit.php?new=1{$urlparamsstr}">{str section=collection tag=newcollection}</a>
        <a class="btn" href="{$WWWROOT}view/choosetemplate.php?searchcollection=1{$urlparamsstr}">{str section=collection tag=copyacollection}</a>
    </div>
{/if}
<p class="intro">{str tag=collectiondescription section=collection}</p>
{if !$canedit}<p>{str tag=canteditgroupcollections section=collection}</p>{/if}
{if $collections}
    <table id="myviews" class="fullwidth listing">
        <tbody>
        {foreach from=$collections item=collection}
                <tr class="{cycle values='r0,r1'}">
                    <td>
                        {if !$collection->submitinfo && $canedit}
                        <div class="fr viewcontrolbuttons">
                            <a href="{$WWWROOT}collection/views.php?id={$collection->id}" title="{str tag=manageviews section=collection}"><img src="{theme_url filename='images/manage.gif'}" alt="{str tag=manageviews section=collection}"></a>
                            <a href="{$WWWROOT}collection/edit.php?id={$collection->id}" title="{str tag=edittitleanddescription section=view}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
                            <a href="{$WWWROOT}collection/delete.php?id={$collection->id}" title="{str tag=deletecollection section=collection}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a>
                        </div>
                        {/if}

            {if $collection->views[0]->view}
                        <h4><a href="{$collection->views[0]->fullurl}">{$collection->name}</a></h4>
            {else}
                        <h4 title="{str tag=emptycollection section=collection}">{$collection->name}</h4>
            {/if}


                        <div class="cb videsc">{$collection->description}</div>

                        <div class="videsc">
                          <div><label>{str tag=Views section=view}:</label>
                          {if $collection->views}
                            {foreach from=$collection->views item=view name=cviews}
                                <a href="{$view->fullurl}">{$view->title}</a>{if !$.foreach.cviews.last}, {/if}
                            {/foreach}
                          {else}
                            {str tag=none}
                          {/if}
                          </div>
                        </div>

            {if $collection->submitinfo}
                        <div class="videsc submitted-viewitem">{str tag=collectionsubmittedtogroupon section=view arg1=$collection->submitinfo->url arg2=$collection->submitinfo->name arg3=$collection->submitinfo->time|format_date}</div>
            {/if}
                    </td>
                </tr>
        {/foreach}
        </tbody>
    </table>
       {$pagination|safe}
{else}
        <div class="message">{str tag=nocollections section=collection}{if $addonelink} <a href={$addonelink}>{str tag=addone}</a>{/if}</div>
{/if}
{include file="footer.tpl"}
