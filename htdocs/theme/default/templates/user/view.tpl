{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
        <div id="userview">
            <h2>
            {$NAME|escape}
            {if $relationship && $relationship != 'none'}
            - {str tag=$relationship section='group'}
            {/if}
            </h2>
            <div>
                <div class="fr">
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=100&amp;id={$USERID}" alt="">
                    {if $canmessage}
                        <br>
                        <a href="{$WWWROOT}user/sendmessage.php?id={$USERID}&amp;returnto=view">{str tag='sendmessage' section='group'}</a>
                    {/if}
                    {if $relationship == 'existingfriend'}
                        <br>
                        <a href="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view">{str tag='removefromfriendslist' section='group'}</a>
                    {elseif $relationship == 'none' && $friendscontrol == 'auto'}
                        {$newfriendform}
                    {elseif $relationship == 'none' && $friendscontrol == 'auth'}
                        <br>
                        <a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view">{str tag='requestfriendship' section='group'}</a>
                    {/if}
                    {if $inviteform}
                        {$inviteform}
                    {/if}
                    {if $addform}
                        {$addform}
                    {/if}
                </div>
                {$introduction}
                {foreach from=$USERFIELDS name=userfields key=key item=item}
                    <p><strong>{str section=artefact.internal tag=$key}:</strong> {$item}</p>
                {/foreach}
                {if $relationship == 'pending'}
                    <div class="message">
                        {str tag='whymakemeyourfriend' section='group'} {$reason}
                        {$requestform}
                    </div>
                {/if}
                <h5>{str section=mahara tag=views}</h6>
                {if $VIEWS}
                    <table>
                    {foreach from=$VIEWS item=item name=view}
                        <tr>
                            <td>
                                <h6><a href="{$WWWROOT}view/view.php?id={$item->id}">{$item->title|escape}</a></h6>
                                <span>
                                {if $item->description}
                                    {$item->description}
                                {/if}
                                {if $item->description && $item->artefacts}<br>{/if}
                                {if $item->artefacts}
                                    <strong>{str tag="artefacts" section="view"}:</strong>
                                    {foreach from=$item->artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$item->id}">{$artefact.title|escape}</a>{if !$smarty.foreach.artefacts.last}, {/if}{/foreach}
                                {/if}
                                </span>
                            </td>
                        </tr>
                    {/foreach}
                    </table>
                {else}
                    {str tag='noviewstosee' section='group'}
                {/if}
                    </ul>
                <h6>{str section=mahara tag=groups}</h6>
                {if $USERGROUPS}
                <table>
                {foreach from=$USERGROUPS item=item}
                    <tr>
                        <td>
                            <h6><a href="{$WWWROOT}group/view.php?id={$item->id}">{$item->name|escape}</a> - {str tag=$item->type section=group}</h6>
                            {if $item->description}
                                {$item->description}
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </table>
                {else}
                    {str tag='notinanygroups' section='group'}
                {/if}
                <h6>{$friendsmessage}</h6>
                <div class="friends">
                <table>
                {foreach from=$friends item=row}
                    <tr>
                    {foreach from=$row item=friend}
                        <td class="friendcell">
                            <a href="{$WWWROOT}user/view.php?id={$friend}">
                               <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=60&amp;id={$friend}" alt="">
                               <br>{$friend|display_name|escape}
                            </a>
                        </td>
                    {/foreach}
                    </tr>
                {/foreach}
                </table>
                </div>
            </div>
        </div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
