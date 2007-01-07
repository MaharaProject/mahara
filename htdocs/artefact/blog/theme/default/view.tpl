{* 

  This template displays a list of the user's blog posts for a particular blog.

 *}

{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
			<h2>{str section="artefact.blog" tag="viewblog"} - {$blog->get('title')|escape}</h2>
			
			<div id="myblogs">
                                <div>
                                    <a href="{$WWWROOT}artefact/blog/settings/?id={$blog->get('id')}">{str section="artefact.blog" tag="settings"}</a>
                                </div>

				<div class="addicon">
					<a href="{$WWWROOT}artefact/blog/editpost.php?blog={$blog->get('id')}">{str section="artefact.blog" tag="addpost"}</a>
				</div>
		
			<table id="postlist" class="tablerenderer">
				<thead>
				</thead>
				<tbody>
				</tbody>
			</table>
				
            <div class="addicon">
                <a href="{$WWWROOT}artefact/blog/editpost.php?blog={$blog->get('id')}">{str section="artefact.blog" tag="addpost"}</a>
            </div>
				
			</div>
				
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
