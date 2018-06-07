{include file="header.tpl"}

                <h1>{str tag="search"}</h1>
                <div class="searchform">
                    <form method="post" onsubmit="dosearch(); return false;">
                        <label for="search_query">{str tag="query"}:</label>
                        <input type="text" name="query" id="search_query" value="{$query}">
                        <button type="submit" class="btn btn-primary">{str tag="go"}</button>
                    </form>
                </div>
                <div id="selfsearchresults">
                    <h2>{str tag="Results"}</h2>
                    <table id="searchresults" class="d-none tablerenderer fullwidth">
                        <tbody>
                        </tbody>
                    </table>
                </div>
{include file="footer.tpl"}
