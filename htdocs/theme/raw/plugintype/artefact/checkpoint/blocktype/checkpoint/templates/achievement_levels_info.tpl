<div class="achievement_help">
  {foreach from=$achievement_levels item=level}
    <p><strong>Level {$level->type}:</strong>
      {$level->value}</p>
  {/foreach}
</div>

<script>
  // show the help modal of checkpoint blocks
  $('form[id^="checkpoint_levels"]').parent().parent().css('overflow', 'visible');
</script>