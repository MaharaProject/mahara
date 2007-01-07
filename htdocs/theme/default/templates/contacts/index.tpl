{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>
{include file="columnleftstart.tpl"}
	  		<h2>{str tag="myfriends"}</h2>
                            <form>
                                <select name="pending" onChange="{$pendingchange}">
                                    <option value="0">{str tag='currentfriends'}</option>
                                    <option value="1">{str tag='pendingfriends'}</option>
                                </select>
                            </form>
                            <div id="messagediv"></div>
                            <table id="friendslist" class="tablerenderer">
	                        <thead>
                                    <tr>
 	                                <td></td>
 	                                <td></td>
 	                                <td></td>
                                    <tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div id="messagediv"></div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
