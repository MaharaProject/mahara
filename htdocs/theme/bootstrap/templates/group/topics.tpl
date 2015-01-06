{include file="header.tpl"}
<p class="intro">{str tag=activetopicsdescription section=interaction.forum}</p>
<table id="topiclist" class="fullwidth">
  <thead>
    <tr>
      <th>{str tag=Topic section=interaction.forum}</th>
      <th class="center">{str tag=Posts section=interaction.forum}</th>
      <th>{str tag=lastpost section=interaction.forum}</th>
    </tr>
  </thead>
  <tbody>
{include file="group/topicrows.tpl"}
  </tbody>
</table>
<div id="topics_pagination">{$pagination|safe}</div>
{include file="footer.tpl"}
