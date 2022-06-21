{include file="header.tpl"}
                <form class="pieform with-heading form-inline searchform" method="post" onsubmit="dosearch(); return false;">
                    <label for="search_query">{str tag="query"}:</label>
                    <input type="text" name="query" id="search_query" value="{$query}" class="form-control input-small text">
                    <button type="submit" class="btn btn-secondary">{str tag="go"}</button>
                </form>
                <div id="selfsearchresults">
                    <h2>{str tag="Results"}</h2>
                    <table id="searchresults" class="d-none tablerenderer fullwidth">
                        <tbody>
                        </tbody>
                    </table>
                </div>
{include file="footer.tpl"}
