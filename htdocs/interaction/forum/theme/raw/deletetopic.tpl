{include file="header.tpl"}

<h2>{$subheading}</h2>
<div class="message delete">{$deleteform|safe}</div>
{include file="interaction:forum:simplepost.tpl" post=$topic groupadmins=$groupadmins}

{include file="footer.tpl"}
