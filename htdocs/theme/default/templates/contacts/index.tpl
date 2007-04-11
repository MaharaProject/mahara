{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
	  		<h2>{str tag="myfriends"}</h2>
                            <form>
                                <select id="pendingopts" name="pending" onChange="pendingChange();">
                                    <option value="0">{str tag='currentfriends'}</option>
                                    <option value="1">{str tag='pendingfriends'}</option>
                                </select>
                            </form>
                            <table id="friendslist" class="hidden tablerenderer">
                                <thead>
                                    <tr>
                                        <th>{str tag="profileicon"}</th>
                                        <th>{str tag="friend"}</th>
                                        <th id="viewsheader">{str tag="views"}</th>
                                        <th id="removeorreason">{str tag="remove"}</th>
                                    <tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div id="messagediv"></div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
