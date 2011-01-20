{include file="header.tpl"}
<p class="intro">{str tag=sharedviewsdescription section=view}</p>
<div>{$searchform|safe}</div>
<table id="sharedviewlist" class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=name}</th>
      <th class="center">{str tag=Comments section=artefact.comment}</th>
      <th>{str tag=lastcomment section=artefact.comment}</th>
    </tr>
  </thead>
  <tbody>
{include file="view/sharedviewrows.tpl"}
  </tbody>
</table>
<div id="sharedviews_pagination">{$pagination|safe}</div>
{include file="footer.tpl"}
