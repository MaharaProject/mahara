{include file="header.tpl"}

{include file="columnfullstart.tpl"}
                <div class="searchform">
                    <h2>{str tag="search"}</h2>
                    <form action="" method="get" onsubmit="doSearch(); return false;">
                    <label>Query: 
                        <input type="text" name="search_query" id="search_query" value="{$search_query_value|escape}">
                    </label>
                    <select id="search_type">
                        <option value="user">{str tag=users}</option>
                        <option value="group">{str tag=groups}</option>
                    </select>
                    <input type="submit" class="submit" value="{str tag='go'}">
                    </form>
				</div>
				<div id="seachresults">
                    <h3>{str tag="results"}</h3>
                    <table id="searchresults" class="hidden tablerenderer">
                        <tbody>
                        </tbody>
                    </table>
				</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

