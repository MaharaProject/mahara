{if $r.contentdata->type eq 'collection'}<span id="exportcontent_{$r.contentdata->id}">{/if}
{if $r.contentdata->url}<a href="{$r.contentdata->url}">{/if}{$r.contentdata->title}{if $r.contentdata->url}</a>{/if}
{if $r.contentdata->type eq 'collection'}</span>{/if}