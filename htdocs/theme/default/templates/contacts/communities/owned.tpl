{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<h2>{str tag="myownedcommunities"}</h2>
                        <div class="addcommunitylink">
                            <span class="addicon">
                                <a href="{$WWWROOT}/contacts/communities/create.php">{str tag='addcommunity'}</a>
                            </span>
                        </div>
                            <table id="communitylist" class="hidden tablerenderer">
	                        <thead>
                                    <tr>
 	                                <th>{str tag='name'}</th>
 	                                <th>{str tag='communitymemberrequests'}
                                        {contextualhelp plugintype='core' pluginname='communities' section='pendingmembershipheader'}
                                    </th>
 	                                <th></th>
                                    <tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div id="messagediv"></div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
