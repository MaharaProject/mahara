{include file="header.tpl"}

{include file="columnfullstart.tpl"}
                <div class="searchform">
                    <h2>{str tag="search"}</h2>
                    <form method="post" onsubmit="dosearch(); return false;">
                        <label>Query: 
                            <input type="text" name="query" id="search_query" value="{$query|escape}">
                        </label>
                        <button type="submit" class="button">{str tag="go"}</button>
                    </form>
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
