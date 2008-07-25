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
                {$introduction}
                <div class="fr user-icon">
                    <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxwidth=100&amp;maxheight=100&amp;id={$USERID}" alt="">
                    <div>
                    {if !empty($loginas)}
                      <div class="center"><strong><a href="{$WWWROOT}admin/users/changeuser.php?id={$USERID}">{$loginas}</a></strong></div>
                    {/if}
                    {if $canmessage}
                        <a href="{$WWWROOT}user/sendmessage.php?id={$USERID}&amp;returnto=view" id="btn-sendmessage">{str tag='sendmessage' section='group'}</a>
                    {/if}
                    {if $relationship == 'existingfriend'}
                        <br>
                        <a href="{$WWWROOT}user/removefriend.php?id={$USERID}&amp;returnto=view" id="btn-delete">{str tag='removefromfriendslist' section='group'}</a>
                    {elseif $relationship == 'none' && $friendscontrol == 'auto'}
                        {$newfriendform}
                    {elseif $relationship == 'none' && $friendscontrol == 'auth'}
                        <br>
                        <a href="{$WWWROOT}user/requestfriendship.php?id={$USERID}&amp;returnto=view" id="btn-request">{str tag='requestfriendship' section='group'}</a>
                    {/if}
                    {if $invitedlist}<div>{str tag=groupinvitesfrom section=group}{$invitedlist}</div>{/if}
                    {if $inviteform}
                        {$inviteform}
                    {/if}
                    {if $requestedlist}<div>{str tag=requestedmembershipin section=group}{$requestedlist}</div>{/if}
                    {if $addform}
                        {$addform}
                    {/if}
                    </div>
                </div>
				<ul id="user-info">
                {foreach from=$USERFIELDS name=userfields key=key item=item}
                    <li><label>{str section=artefact.internal tag=$key}:</label> {$item}</li>
                {/foreach}
				</ul>
                {if $relationship == 'pending'}
                    <div class="message">
                        {str tag='whymakemeyourfriend' section='group'} {$reason}
                        {$requestform}
                    </div>
                {/if}
                <h3>{str section=mahara tag=views}</h3>
                {if $VIEWS}
                    <table id="userviewstable">
                    {foreach from=$VIEWS item=item name=view}
                        <tr>
                            <td class="r{cycle values=0,1}">
                                <h4><a href="{$WWWROOT}view/view.php?id={$item->id}">{$item->title|escape}</a></h4>
                                <span>
                                {if $item->description}
                                    {$item->description}
                                {/if}
                                {if $item->description && $item->artefacts}<br>{/if}
                                {if $item->artefacts}
                                    <strong>{str tag="artefacts" section="view"}:</strong>
                                    {foreach from=$item->artefacts item=artefact name=artefacts}<a href="{$WWWROOT}view/artefact.php?artefact={$artefact.id}&amp;view={$item->id}" class="link-artefacts">{$artefact.title|escape}</a>{if !$smarty.foreach.artefacts.last}, {/if}{/foreach}
                                {/if}
                                </span>
                            </td>
                        </tr>
                    {/foreach}
                    </table>
                {else}
                    {str tag='noviewstosee' section='group'}
                {/if}
                <h3>{str section=mahara tag=groups}</h3>
                {if $USERGROUPS}
                <table id="usergroupstable">
                {foreach from=$USERGROUPS item=item}
                    <tr>
                        <td class="r{cycle values=0,1}">
                            <h4><a href="{$WWWROOT}group/view.php?id={$item->id}">{$item->name|escape}</a> - {$item->roledesc}</h4>
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
                <h3>{$friendsmessage}</h3>
                <div class="friends">
                {if $friends}
                    <table id="userfriendstable">
                    {foreach from=$friends item=row}
                        <tr>
                        {foreach from=$row item=friend}
                            <td class="r{cycle values=0,1} friendcell">
                                <a href="{$WWWROOT}user/view.php?id={$friend}">
                                   <img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxwidth=60&amp;maxheight=60&amp;id={$friend}" alt="">
                                   <br>{$friend|display_name|escape}
                                </a>
                            </td>
                        {/foreach}
                        </tr>
                    {/foreach}
                    </table>
                {else}
                    {str tag='nofriends' section='group'}
                {/if}
                </div>
            </div>
        </div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
