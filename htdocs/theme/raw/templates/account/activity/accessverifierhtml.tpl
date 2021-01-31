{* Customisation for PCNZ WR 349183 *}
<p>{str section='accessverifier' tag='notificationheader', arg1=$pharmacistname}</p>

<p>{str section='accessverifier' tag='notificationmsg', arg1=$pharmacistname}</p>

{foreach from=$accessitems item=item}
<p>{str section='accessverifier' tag='access'} '<a href={$item.url}>{$item.name|clean_html|safe}</a>'.</p>
{/foreach}

<p>{str section='accessverifier' tag='thankyoumsg'}</p>

{* End customisation *}
