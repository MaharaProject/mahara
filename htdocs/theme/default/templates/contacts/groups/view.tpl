{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name}</h2>
                
                <p>{str tag='owner'}: {$group->ownername}</p>
	        {assign var="jointype" value=$group->jointype}
	        {assign var="joinstr" value=groupjointype$jointype}
                <p>{str tag=$joinstr}</p>
                {if $group->description} <p>{$group->description}</p> {/if}
                {if $canleave} <p><a href="view.php?id={$group->id}&amp;joincontrol=leave">{str tag='leavegroup'}</a></p>
                {elseif $canrequestjoin} <p id="joinrequest"><a href="" onClick="return joinRequestControl();">{str tag='requestjoingroup'}</a></p>
                {elseif $canjoin} <p><a href="view.php?id={$group->id}&amp;joincontrol=join"">{str tag='joingroup'}</a></p>
                {elseif $canacceptinvite} <p>{str tag='grouphaveinvite'} <a href="view.php?id={$group->id}&amp;joincontrol=acceptinvite">{str tag='acceptinvitegroup'}</a> | <a href="view.php?id={$group->id}&amp;joincontrol=declineinvite">{str tag='declineinvitegroup'}</a></p>{/if}
                {if $member}
                    <div class="groupviews">
                        <h5>{str tag='views'}</h5>
                        {if $tutor && $controlled}
                            <form>
                                <select name="submitted" onChange="viewlist.submitted=this.options[this.selectedIndex].value;viewlist.doupdate();">
                                    <option value="0">{str tag='allviews'}</option>
                                    <option value="1">{str tag='submittedviews'}</option>
                                </select>
                            </form>
                        {/if}
                        <table id="group_viewlist">
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
                    <div class="groupmembers">
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
                             <input type="button" class="button" value="{str tag='updatemembership'}" onClick="return updateMembership();" id="groupmembers_update">
                         {/if}
                     </div>
                {/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
