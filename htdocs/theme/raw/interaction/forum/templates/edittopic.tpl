{include file="header.tpl"}
{if $timeleft}<div class="fr timeleftnotice">{str tag="timeleftnotice" section="interaction.forum" args=$timeleft}</div>{/if}
<h2>{$subheading}</h2>
{$editform|safe}
{include file="footer.tpl"}
