{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<h2>{str tag="myownedgroups"}</h2>
                        <div class="addgrouplink">
                            <span class="addicon">
                                <a href="{$WWWROOT}/contacts/groups/create.php">{str tag='addgroup'}</a>
                            </span>
                        </div>
                            <table id="grouplist" class="hidden tablerenderer">
	                        <thead>
                                    <tr>
 	                                <th>{str tag='name'}</th>
 	                                <th>{str tag='groupmemberrequests'}
                                        {contextualhelp plugintype='core' pluginname='groups' section='pendingmembershipheader'}
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
