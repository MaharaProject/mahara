{include file="header.tpl"}
		<div id="myblogs rel">
            <div class="rbuttons">
                <a class="btn-add" href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
            </div>
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
