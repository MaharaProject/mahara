{* 

  This template displays a list of the user's blogs.  The list is populated
  using javascript.

 *}

{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
			<div id="myblogs">
    		<h2>{str section="artefact.blog" tag="myblogs"}</h2>
    			<div class="addicon">
        		<a href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
    			</div>

			<table id="bloglist" class="tablerenderer">
				<thead>
					<tr>
						<th>{str section="artefact.blog" tag="title"}</th>
						<th>{str section="artefact.blog" tag="description"}</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
    
				<div class="addicon">
					<a href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
				</div>
				
			</div>
				
			</div>
		</span></span></span></span></div>
	</div>
</div>

{include file="footer.tpl"}
