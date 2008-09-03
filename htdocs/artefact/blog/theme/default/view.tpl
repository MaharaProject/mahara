{* 

  This template displays a list of the user's blog posts for a particular blog.

 *}

{include file="header.tpl"}

{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<div id="myblogs">

                {$blog->get('description')}

                    <span class="addicon fr">
                        <a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">{str section="artefact.blog" tag="addpost"}</a>
                        <a href="{$WWWROOT}artefact/blog/settings/?id={$blog->get('id')}">{str section="artefact.blog" tag="settings"}</a>
                    </span>
		
                {if $blog->count_children() > 0}
				<div><table id="postlist" class="hidden tablerenderer">
					<tbody>
									  <tr><td></td><td></td><td></td></tr>
					</tbody>
				</table></div>
                {else}
                <div class="message">{$strnopostsaddone}</div>
                {/if}
					
            </div>
				
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
