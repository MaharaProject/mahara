{include file="header.tpl"}
			<div id="myblogs rel">

				<div class="rbuttons">
                    <div><a class="btn-add" href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">{str section="artefact.blog" tag="addpost"}</a></div>
					<div><a class="btn-edit" href="{$WWWROOT}artefact/blog/settings/?id={$blog->get('id')}">{str section="artefact.blog" tag="settings"}</a></div>
					<div><a class="btn-add" href="{$WWWROOT}artefact/blog/new">{str tag=addblog section=artefact.blog}</a></div>
				</div>

                <p>{$blog->get('description')}</p>
		
                {if $blog->count_children() > 0}
				<div><table id="postlist" class="hidden tablerenderer fullwidth nohead">
					<tbody>
							  <tr><td></td><td></td><td></td></tr>
					</tbody>
				</table></div>
                {else}
                <div class="message">{$strnopostsaddone}</div>
                {/if}
					
            </div>
{include file="footer.tpl"}
