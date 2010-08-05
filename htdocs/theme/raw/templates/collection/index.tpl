{include file="header.tpl"}
    <div class="rbuttons">
        <a class="btn btn-add" href="{$WWWROOT}collection/edit.php?new=1">{str section=collection tag=newcollection}</a>
    </div>
{if $collections}
    <table id="myviews" class="fullwidth listing">
        <tbody>
        {foreach from=$collections item=collection}
                <tr class="{cycle values='r0,r1'}">
                    <td><div class="rel">

                        <h3><a href="{$WWWROOT}collection/about.php?id={$collection->id}">{$collection->name}</a></h3>

                        <div class="rbuttons"><a href="{$WWWROOT}collection/delete.php?id={$collection->id}" class="btn-del">{str tag=delete}</a></div>

                        <div class="vi">
                            <h4><a href="{$WWWROOT}collection/edit.php?id={$collection->id}" id="editcollectiondetails">{str tag="edittitleanddesc" section="collection"}</a></h4>

                            <div class="videsc">{$collection->description}</div>
                        </div>

                    <div class="vi">
                        <h4><a href="{$WWWROOT}collection/views.php?id={$collection->id}" id="editcollectionviews">{str tag=manageviews section="collection"}</a></h4>
                    </div>

                    <div class="vi">
                        <h4><a href="{$WWWROOT}view/access.php?collection={$collection->id}" id="editcollectionaccess">{str tag="editaccess" section="collection"}</a></h4>
                    </div>

                    </div></td>
                </tr>
        {/foreach}
        </tbody>
    </table>
       {$pagination|safe}
{else}
        <div class="message">{$strnocollectionsaddone|safe}</div>
{/if}
{include file="footer.tpl"}
