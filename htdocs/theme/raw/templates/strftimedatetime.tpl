{assign var="time" value=$r.$f}
{if $time}
{$time|strtotime|format_date:'strftimedatetime'}
{/if}
