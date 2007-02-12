{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
                <h2>{$community->name}</h2>
                
                <p>{str tag='owner'}: {$community->ownername}</p>
	        {assign var="jointype" value=$community->jointype}
	        {assign var="joinstr" value=communityjointype$jointype}
                <p>{str tag=$joinstr}</p>
                {if $community->description} <p>{$community->description}</p> {/if}
                {if $canleave} <p><a href="view.php?id={$community->id}&amp;joincontrol=leave">{str tag='leavecommunity'}</a></p>
                {elseif $canrequestjoin} <p id="joinrequest"><a href="" onClick="return joinRequestControl();">{str tag='requestjoincommunity'}</a></p>
                {elseif $canjoin} <p><a href="view.php?id={$community->id}&amp;joincontrol=join"">{str tag='joincommunity'}</a></p>
                {elseif $canacceptinvite} <p>{str tag='communityhaveinvite'} <a href="view.php?id={$community->id}&amp;joincontrol=acceptinvite">{str tag='acceptinvitecommunity'}</a> | <a href="view.php?id={$community->id}&amp;joincontrol=declineinvite">{str tag='declineinvitecommunity'}</a></p>{/if}
                {if $member}
                    <div id="communitywatchlistcontrol">
                    <input type="button" id="watchlistcontrolbutton" class="button" onclick="return toggleWatchlist();" value="{if $onwatchlist}{str tag=removefromwatchlist section=activity}{else}{str tag=addtowatchlist section=activity}{/if}">
                    </div>
                    <div class="communityviews">
                        <h5>{str tag='views'}</h5>
                        {if $tutor && $controlled}
                            <form>
                                <select name="submitted" onChange="viewlist.submitted=this.options[this.selectedIndex].value;viewlist.doupdate();">
                                    <option value="0">{str tag='allviews'}</option>
                                    <option value="1">{str tag='submittedviews'}</option>
                                </select>
                            </form>
                        {/if}
                        <table id="community_viewlist">
                            <thead>
                                <tr>
                                    <th>{str tag='name'}</th>
                                    <th>{str tag='owner'}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>                   
                    <div class="communitymembers">
                    <a name="members"></a>
                        <h5>{str tag='members'}</h5>
                        {if $canupdate && $request}
                            <form>
                                <select id="pendingselect" name="pending" onChange="switchPending();">
                                    <option value="0">{str tag='members'}</option>
                                    <option value="1">{str tag='memberrequests'}</option>
                                </select>
                            </form>
                         {/if}
                         <table id="memberlist">
                             <thead>
                                 <tr>
                                     <th>{str tag='name'}</th>
                                     <th id="pendingreasonheader">{str tag='reason'}</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>
	                 {if $canupdate}
                             <input type="button" class="button" value="{str tag='updatemembership'}" onClick="return updateMembership();" / id="communitymembers_update">
                         {/if}
                     </div>
                {/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
