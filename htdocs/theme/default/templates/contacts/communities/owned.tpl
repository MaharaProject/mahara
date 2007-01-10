{include file="header.tpl"}
<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
			<h2>{str tag="myownedcommunities"}</h2>
                        <div class="addcommunitylink">
                           <a href="{$WWWROOT}/contacts/communities/edit.php?new=1">{str tag='addcommunity'}</a>
                        </div>
                            <table id="communitylist">
	                        <thead>
                                    <tr>
 	                                <th>{str tag='name'}</th>
 	                                <th>{str tag='communitymemberrequests'}</th>
 	                                <th></th>
                                    <tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div id="messagediv"></div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
