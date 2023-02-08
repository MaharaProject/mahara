<div class="achievement_help">
    <p>{str tag=achievementlevel section='artefact.checkpoint'}: {$configdata.level}</p>
    <p>{str tag=achievementauthor section='artefact.checkpoint'}: {$configdata.author}</p>
    <p>{str tag=achievementleveltime section='artefact.checkpoint'}: {$configdata.time}</p>
</div>

<script>
// show the help modal of checkpoint blocks
$('form[id^="checkpoint_levels"]').parent().parent().css('overflow', 'visible');
</script>
