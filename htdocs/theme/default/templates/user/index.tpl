{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
	  		<h2>{str tag="myfriends"}</h2>

            <div id="friendslistcontainer">
            <table id="friendslistcontrols">
                <tr>
                    <td>
                        <select id="filter" name="filter" onchange="filterChange();">
                            <option value="0">{str tag='allfriends'}</option>
                            <option value="1">{str tag='currentfriends'}</option>
                            <option value="2">{str tag='pendingfriends'}</option>
                        </select>
                        <a href="" onclick="showFriendslist(); return false;" class="hidden" id="backlink">&laquo; {str tag="backtofriendslist"}</a>
                    </td>
                    <td class="right">
                        <form action="" method="post" onsubmit="searchUsers(); return false;">
                            <input type="text" class="text" name="search" id="friendsquery">
                            <input type="submit" class="submit" value="{str tag="findnewfriends"}">
                        </form>
                    </td>
                </tr>
            </table>
            <div id="friendmessage" class="message hidden"></div>
            <table id="friendslist" class="hidden tablerenderer">
                <tbody>
                </tbody>
            </table>
            <table id="searchresults" class="hidden tablerenderer">
                <tbody>
                </tbody>
            </table>
            </div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
