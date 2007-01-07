{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
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
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
