{* Customisation for PCNZ WR 349183 *}
<p>{str section='accessverifier' tag='notificationheader', arg1=$pharmacistname}</p>

<p>{str section='accessverifier' tag='notificationmsg', arg1=$pharmacistname}</p>

{foreach from=$accessitems item=item}
<p>{str section='accessverifier' tag='accessat'} {$item.url}</p>
{/foreach}

<p>{str section='accessverifier' tag='thankyoumsg'}</p>

{* End customisation *}
