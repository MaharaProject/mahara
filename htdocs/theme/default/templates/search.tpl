{include file="header.tpl"}

{include file="columnfullstart.tpl"}
                <div class="searchform">
                    <h2>{str tag="search"}</h2>
                    <label>Query: 
                        <input type="text" name="search_query" id="search_query">
                    </label>
                    <select id="search_type">
                        <option value="user">{str tag=users}</option>
                        <option value="community">{str tag=communities}</option>
                    </select>
                    <button type="button" onclick="doSearch();">{str tag="go"}</button>
				</div>
				<div id="seachresults">
                    <h3>{str tag="results"}</h3>
                    <table id="searchresults" class="tablerenderer">
                        <tbody>
                        </tbody>
                    </table>
				</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

