{include file="header.tpl"}
			<div id="myblogs rel">

				{if !$blog->get('locked')}
                <div class="rbuttons">
                    <a class="btn btn-settings" href="{$WWWROOT}artefact/blog/settings/?id={$blog->get('id')}">{str section="artefact.blog" tag="settings"}</a>
                    <a class="btn btn-add" href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">{str section="artefact.blog" tag="addpost"}</a>
				</div>
				{/if}

                <p>{clean_html($blog->get('description'))|safe}</p>
                {if $blog->get('tags')}<p class="tags">{str tag=tags}: {list_tags owner=$blog->get('owner') tags=$blog->get('tags')}</p>{/if}

                {if $blog->count_children() > 0}
				<table id="postlist" class="hidden tablerenderer fullwidth nohead">
					<tbody>
							  <tr><td></td><td></td><td></td></tr>
					</tbody>
				</table>
                {else}
                <div class="message">
                  {str tag=nopostsyet section=artefact.blog} {if !$blog->get('locked')}<a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">{str tag=addone section=artefact.blog}</a>{/if}
                </div>
                {/if}

                {if $enablemultipleblogstext}
                <p class="s dull center">{str tag=enablemultipleblogstext section=artefact.blog arg1=$WWWROOT}</p>
                {/if}
            </div>
{include file="footer.tpl"}

