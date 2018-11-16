{include file="header.tpl"}
{if $timeleft}
    <div class="alert alert-warning" id="timeleft">{str tag="timeleftnotice1" section="interaction.forum" args=$timeleft}</div>
    <div class="timeexpired hidden">{if $moderator}{str tag="timeleftnoticeexpiredmoderator" section="interaction.forum"}{else}{str tag="timeleftnoticeexpired" section="interaction.forum"}{/if}</div>
{/if}
<h2>{$subheading}</h2>

{if $timeleft}
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
{/if}

{$editform|safe}
{include file="footer.tpl"}
