{$time = $r.$f}
{if $time}
{$time|strtotime|format_date:'strftimedatetime'}
{/if}
