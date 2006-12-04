{* 

  This template displays a list of the user's blogs.  The list is populated
  using javascript.

 *}{include file="header.tpl"}
{include file="adminmenu.tpl"}

<div class="content">
    <h2>{str section="artefact.blog" tag="blogs"}</h2>

    <div class="newblog">
        <a href="{$WWWROOT}/artefact/blog/new/">{str section="artefact.blog" tag="newblog"}</a>
    </div>

    <table id="bloglist">
        <thead>
            <tr>
                <th>{str section="artefact.blog" tag="title"}</th>
                <th>{str section="artefact.blog" tag="description"}</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    
    <div class="newblog">
        <a href="{$WWWROOT}/artefact/blog/new/">{str section="artefact.blog" tag="newblog"}</a>
    </div>
</div>

{include file="footer.tpl"}
