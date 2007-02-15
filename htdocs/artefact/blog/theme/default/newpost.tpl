{*

  This template displays the 'new blog post' form

 *}

{include file="header.tpl"}
 
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
    		<h2>{str section="artefact.blog" tag="newblogpost"}</h2>
    		{$newpostform}

    		ADD RADIO BUTTONS

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
