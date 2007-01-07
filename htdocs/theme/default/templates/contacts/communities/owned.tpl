{include file="header.tpl"}
<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
	
			<h2>{str tag="myownedcommunities"}</h2>
                        <div class="addcommunitylink">
                           <a href="{$WWWROOT}/contacts/communities/edit.php?new=1">{str tag='addcommunity'}</a>
                        </div>
                            <table id="communitylist" class="tablerenderer">
	                        <thead>
                                    <tr>
 	                                <th>{str tag='name'}</th>
 	                                <th>{str tag='communitymemberrequests'}</th>
 	                                <td></td>
                                    <tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div id="messagediv"></div>
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
