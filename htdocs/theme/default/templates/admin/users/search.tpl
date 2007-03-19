{include file="header.tpl"}

<div id="column-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
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
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}

