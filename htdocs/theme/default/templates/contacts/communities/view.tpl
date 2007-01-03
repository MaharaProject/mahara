{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
    <div class="content">
        <div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
            <div class="maincontent">
                <h2>{$community->name}</h2>
                <p>{str tag='owner'}: {$community->ownername}</p>
                {if $community->description} <p>{$community->description}</p> {/if}
                {if $canleave} <p><a href="" onClick="return memberControl({$community->id}, 'leave');">{str tag='leavecommunity'}</a></p>
                {elseif $canrequestjoin} <p><a href="" onClick="return memberControl({$community->id}, 'request');">{str tag='requestjoincommunity'}</a></p>
                {elseif $canjoin} <p><a href="" onClick="return memberControl({$community->id}, 'join');">{str tag='joincommunity'}</a></p>
                {elseif $canacceptinvite} <p><a href="" onClick="return memberControl({$community->id}, 'invite');">{str tag='invitedjoincommunity'}</a></p>{/if}
                {if $member}
                    <div class="communityviews">
                        <h5>{str tag='views'}</h5>
                        {if $tutor}
                            <form>
                                <select name="submitted" onChange="viewlist.submitted=this.options[this.selectedIndex].value;viewlist.doupdate();">
                                    <option value="0">{str tag='allviews'}</option>
                                    <option value="1">{str tag='submittedviews'}</option>
                                </select>
                            </form>
                        {/if}
                        <table id="viewlist">
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
                        <h5>{str tag='members'}</h5>
                        {if $tutor}
                            <form>
                                <select name="pending" onChange="memberlist.pending=this.options[this.selectedIndex].value;memberlist.doupdate();">
                                    <option value="0">{str tag='members'}</option>
                                    <option value="1">{str tag='memberrequests'}</option>
                                </select>
                            </form>
                         {/if}
                         <table id="memberlist">
                             <thead>
                                 <tr>
                                     <th>{str tag='name'}</th>
                                 </tr>
                             </thead>
                             <tbody>
                             </tbody>
                         </table>
                         <input type="button" value="{str tag='updatemembership'}" onClick="return updateMembership();" />
                         <div id="messagediv"></div>
                     </div>
                {/if}
            </div>
        </span></span></span></span></div>	
    </div>
</div>

{include file="footer.tpl"}
