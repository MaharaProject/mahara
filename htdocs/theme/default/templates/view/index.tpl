{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                        <h2>{str tag='myviews' section='view'}</h2>
    			<div id="views">
        			<div style="text-align:right;">
                    <form action="{$WWWROOT}view/edit.php" method="GET">
                        <input class="button" type="submit" value="{str tag="createview" section="view"}" id="createnew">
                        {contextualhelp plugintype='core' pluginname='views' section='createviewbutton'}
                    </form>
        			</div>
					<table id="viewlist">
						<tbody>
						</tbody>
					</table>
    			</div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
