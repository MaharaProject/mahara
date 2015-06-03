{include file="header.tpl"}
<h2>{$subheading}</h2>

{$reportform|safe}

{include file="interaction:forum:simplepost.tpl" post=$post groupadmins=$groupadmins}

{include file="footer.tpl"}
