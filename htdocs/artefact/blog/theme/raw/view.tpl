{include file="header.tpl"}
			<div id="myblogs">

				<span class="addicon fr">
					<a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">{str section="artefact.blog" tag="addpost"}</a>
					<a href="{$WWWROOT}artefact/blog/settings/?id={$blog->get('id')}">{str section="artefact.blog" tag="settings"}</a>
				</span>

                <p>{$blog->get('description')}</p>
		
                {if $blog->count_children() > 0}
				<div><table id="postlist" class="hidden tablerenderer fullwidth">
					<tbody>
							  <tr><td></td><td></td><td></td></tr>
					</tbody>
				</table></div>
                {else}
                <div class="message">{$strnopostsaddone}</div>
                {/if}
					
            </div>
{include file="footer.tpl"}
