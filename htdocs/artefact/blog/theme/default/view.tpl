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
                    <div class="blogsettingscontainer">
								<div class="addiconcontainer">
                                                                    <span class="addicon">
									<a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">{str section="artefact.blog" tag="addpost"}</a>
                                                                    </span>
                                                                    {contextualhelp plugintype='artefact' pluginname='blog' section='blogaddpostbutton'}
								</div>
                      <span class="blogsettings">  
                         <a href="{$WWWROOT}artefact/blog/settings/?id={$blog->get('id')}">{str section="artefact.blog" tag="settings"}</a>
                      </span>
                      {contextualhelp plugintype='artefact' pluginname='blog' section='blogsettingsbutton'}
                    </div>

		
				<div><table id="postlist" class="tablerenderer">
					<tbody>
									  <tr><td></td><td></td><td></td></tr>
					</tbody>
				</table></div>
					
				</div>
				
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
