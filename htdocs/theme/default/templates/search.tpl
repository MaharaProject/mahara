{include file="header.tpl"}

<div id="column-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
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
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}

