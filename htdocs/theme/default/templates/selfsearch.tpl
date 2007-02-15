{include file="header.tpl"}

{include file="columnfullstart.tpl"}
                <div class="searchform">
                    <h2>{str tag="search"}</h2>
                    <label>Query: 
                        <input type="text" name="search_query" id="search_query" value="{$query|escape}">
                    </label>
                    <button type="button" onclick="doSearch();">{str tag="go"}</button>
				</div>
				<div id="selfsearchresults">
                    <h3>{str tag="results"}</h3>
                    <table id="searchresults" class="tablerenderer">
                        <tbody>
                        </tbody>
                    </table>
				</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}
