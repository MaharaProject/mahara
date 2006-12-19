{*

  This template displays the 'edit blog post' form

 *}

{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
  <div class="content">
    <div class="box-cnrs">
      <span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
      <div class="maincontent">
        <h2>{str section="artefact.blog" tag=$pagetitle}</h2>
        {$textinputform}
        <h3>{str section=artefact.blog tag=attachedfiles}</h3>
        <div id='uploader'></div>
        <table id='attachedfiles'><tbody><tr><td></td></tr></tbody></table>
      </div>
      </span></span></span></span>
    </div>
  </div>
</div>

{include file="footer.tpl"}
