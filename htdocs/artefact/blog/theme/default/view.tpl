{* 

  This template displays a list of the user's blog posts for a particular blog.

 *}

{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
			<h2>{$blog->get('title')}</h2>
			
			<div id="myblogs">
				<div class="newpost">
					<a href="{$WWWROOT}/artefact/blog/newpost/?id={$blog->get('id')}">{str section="artefact.blog" tag="newpost"}</a>
				</div>
		
			<table id="postlist">
				<thead>
				</thead>
				<tbody>
				</tbody>
			</table>
				
				<div class="newpost">
					<a href="{$WWWROOT}/artefact/blog/newpost/?id={$blog->get('id')}">{str section="artefact.blog" tag="newpost"}</a>
				</div>
				
			</div>
				
			</div>
		</span></span></span></span></div>
	</div>
</div>

{include file="footer.tpl"}