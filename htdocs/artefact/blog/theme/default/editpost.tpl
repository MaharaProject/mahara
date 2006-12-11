{*

  This template displays the 'edit blog post' form

 *}{include file="header.tpl"}
{include file="adminmenu.tpl"}

<div class="content">
    <h2>{str section="artefact.blog" tag="editblogpost"}</h2>
    {$editpostform}
</div>

{include file="footer.tpl"}
