{include file="header.tpl"}

{include file="columnfullstart.tpl"}
                <div class="searchform">
                    <h2>{str tag="usersearch" section="admin"}</h2>
                    <label>Query: 
                        <input type="text" name="usersearch" id="usersearch">
                        <button type="button" onclick="doSearch();">{str tag="go"}</button>
                    </label>
				</div>
				<div id="results">
                    <h3>{str tag="results"}</h3>
                    <table id="searchresults" class="hidden tablerenderer">
                        <tbody>
                        </tbody>
                    </table>
				</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

