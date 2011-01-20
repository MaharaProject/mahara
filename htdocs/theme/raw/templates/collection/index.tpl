{include file="header.tpl"}
    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}collection/edit.php?new=1">{str section=collection tag=newcollection}</a>
    </div>
<p class="intro">{str tag=collectiondescription section=collection}</p>
{if $collections}
    <table id="myviews" class="fullwidth listing">
        <tbody>
        {foreach from=$collections item=collection}
                <tr class="{cycle values='r0,r1'}">
                    <td>
                        <div class="fr viewcontrolbuttons">
                            <a href="{$WWWROOT}collection/views.php?id={$collection->id}" title="{str tag=manageviews section=collection}"><img src="{theme_url filename='images/manage.gif'}" alt="{str tag=manageviews}"></a>
                            <a href="{$WWWROOT}collection/edit.php?id={$collection->id}" title="{str tag=edittitleanddescription section=view}"><img src="{theme_url filename='images/edit.gif'}" alt="{str tag=edit}"></a>
                            <a href="{$WWWROOT}collection/delete.php?id={$collection->id}" title="{str tag=deletecollection section=collection}"><img src="{theme_url filename='images/icon_close.gif'}" alt="{str tag=delete}"></a>
                        </div>
            {if $collection->views[0]->view}
                        <h3><a href="{$WWWROOT}view/view.php?id={$collection->views[0]->view}">{$collection->name}</a></h3>
            {else}
                        <h3 title="{str tag=emptycollection section=collection}">{$collection->name}</h3>
            {/if}


                        <div class="cb videsc">{$collection->description}</div>

                        <div class="videsc">
                          <div><label>{str tag=Views section=view}:</label>
                          {if $collection->views}
                            {foreach from=$collection->views item=view name=cviews}
                                <a href="{$WWWROOT}view/view.php?id={$view->view}">{$view->title}</a>{if !$.foreach.cviews.last}, {/if}
                            {/foreach}
                          {else}
                            {str tag=none}
                          {/if}
                          </div>
                        </div>
                    </td>
                </tr>
        {/foreach}
        </tbody>
    </table>
       {$pagination|safe}
{else}
        <div class="message">{$strnocollectionsaddone|safe}</div>
{/if}
{include file="footer.tpl"}
