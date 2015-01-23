{include file="header.tpl"}
<p class="intro">{str tag=sharedviewsdescription section=view}</p>
<div>{$searchform|safe}</div>
<div class="table-responsive">
<table id="sharedviewlist" class="fullwidth table table-striped">
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
</div>
{$pagination|safe}
{include file="footer.tpl"}
