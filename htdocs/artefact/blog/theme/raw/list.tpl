{include file="header.tpl"}
		<div id="myblogs">
              <span class="addicon">
                <a href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
              </span>
			<table id="bloglist" class="hidden tablerenderer fullwidth">
				<thead>
					<tr>
						<th>{str section="artefact.blog" tag="title"}</th>
						<th>{str section="artefact.blog" tag="description"}</th>
                        <th></th>
                        <th></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
				
        </div>
{include file="footer.tpl"}
