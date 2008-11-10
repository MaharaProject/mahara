{* 

  This template displays a list of the user's blogs.  The list is populated
  using javascript.

 *}

{include file="header.tpl"}

{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
		<div id="myblogs">
              <span class="addicon fr">
                <a href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
              </span>
			<table id="bloglist" class="hidden tablerenderer">
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
{include file="columnleftend.tpl"}
		
{include file="footer.tpl"}
