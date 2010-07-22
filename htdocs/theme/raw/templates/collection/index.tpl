{auto_escape on}
{include file="header.tpl"}
    <div class="rbuttons">
        <a class="btn btn-add" href="{$WWWROOT}collection/create.php">{str section="collection" tag="newcollection"}</a>
    </div>
{if !$collections}
        <div class="message">{$strnocollectionsaddone|safe}</div>
{else}
    <table id="mycollections" class="fullwidth listing">
        <tbody>
        {foreach from=$collections item=collection}
                <tr class="{cycle values='r0,r1'}">
                    <td>
                        <div class="fr">
                            <ul class="groupuserstatus">
                                <li><a href="{$WWWROOT}collection/edit.php?id={$collection->id|escape}" class="btn-edit">{str tag="edit"}</a></li>
                                <li><a href="{$WWWROOT}collection/delete.php?id={$collection->id|escape}" class="btn-del">{str tag="delete"}</a></li>
                            </ul>
                        </div>

                        <h3><a href="{$WWWROOT}collection/about.php?id={$collection->id|escape}">{$collection->name|escape}</a></h3>

                    <div class="codesc">{$collection->description}</div>
                    <div class="fl">
                        <ul class="collectionlist">
                            <li><a href="{$WWWROOT}collection/views.php?id={$collection->id|escape}">{str tag="manageviews" section="collection"}</a></li>
                        </ul>
                    </td>
                    </div>
                </tr>
        {/foreach}
        </tbody>
    </table>
       {$pagination|safe}
{/if}
{include file="footer.tpl"}
{auto_escape off}
