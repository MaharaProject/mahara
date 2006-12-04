{* 

  This template displays a list of the user's blog posts for a particular blog.

 *}{include file="header.tpl"}
{include file="adminmenu.tpl"}

<div class="content">
    <h2>{$blog->get('title')}</h2>

    <div>
        <a href="{$WWWROOT}/artefact/blog/newpost/?id={$blog->get('id')}">{str section="artefact.blog" tag="newpost"}</a>
    </div>

    <table id="postlist">
        <thead>
        </thead>
        <tbody>
        </tbody>
    </table>
    
    <div>
        <a href="{$WWWROOT}/artefact/blog/newpost/?id={$blog->get('id')}">{str section="artefact.blog" tag="newpost"}</a>
    </div>
</div>
