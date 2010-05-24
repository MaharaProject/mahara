{include file="header.tpl"}

<h2><a href="{$WWWROOT}interaction/forum/topic.php?id={$parent->topic}">{$parent->topicsubject}</a> - {$action}</h2>

{$editform|safe}

<h4>{str tag="replyto" section="interaction.forum"}</h4>
{include file="interaction:forum:simplepost.tpl" post=$parent groupadmins=$groupadmins}

{include file="footer.tpl"}
