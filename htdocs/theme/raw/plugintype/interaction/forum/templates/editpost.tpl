{include file="header.tpl"}
{if $timeleft}
    <div class="alert alert-warning" id="timeleft">{str tag="timeleftnotice1" section="interaction.forum" args=$timeleft}</div>
    <div class="timeexpired hidden">{if $moderator}{str tag="timeleftnoticeexpiredmoderator" section="interaction.forum"}{else}{str tag="timeleftnoticeexpired" section="interaction.forum"}{/if}</div>
{/if}
<h2><a href="{$WWWROOT}interaction/forum/topic.php?id={$parent->topic}">{$parent->topicsubject}</a> - {$action}</h2>

<script type='application/javascript'>
// Set the number we're counting down from
var countfrom = {$timeleft};

// Update the count down every 1 minute
var x = setInterval(function() {
    countfrom = countfrom -1;
    jQuery('#timeleft .num').html(countfrom);
    // If the count down is finished, write some text
    if (countfrom <= 0) {
        clearInterval(x);
        jQuery('#timeleft').html(jQuery('.timeexpired').html());
    }
}, 60000);
</script>

{$editform|safe}

<div class="replyto"><h4>{str tag="replyto" section="interaction.forum"}</h4>
{include file="interaction:forum:simplepost.tpl" post=$parent groupadmins=$groupadmins}
</div>
{include file="footer.tpl"}
