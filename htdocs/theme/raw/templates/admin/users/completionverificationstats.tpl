{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.firstname}<td><a href="{$item->profileurl}">{$item->firstname}</a></td>{/if}
    {if $columns.lastname}<td><a href="{$item->profileurl}">{$item->lastname}</a></td>{/if}
    {if $columns.displayname}<td><a href="{$item->profileurl}">{$item->displayname}</a></td>{/if}
    {if $columns.username}<td><a href="{$item->profileurl}">{$item->username}</a></td>{/if}
    {if $columns.registration_number}<td>{$item->registration_number}</td>{/if}
    {if $columns.email}<td>{$item->email}</td>{/if}
    {if $columns.portfoliotitle}<td>{$item->portfoliotitle}</td>{/if}
    {if $columns.portfoliocreationdate}<td>{$item->portfoliocreationdate}</td>{/if}
    {if $columns.templatetitle}<td>{if $item->templatetitleurl}<a href="{$WWWROOT}{$item->templatetitleurl}">{$item->templatetitle}</a>{else}{$item->templatetitle}{/if}</td>{/if}
    {if $columns.verifierfirstname}<td><a href="{$item->verifierprofileurl}">{$item->verifierfirstname}</a></td>{/if}
    {if $columns.verifierlastname}<td><a href="{$item->verifierprofileurl}">{$item->verifierlastname}</a></td>{/if}
    {if $columns.verifierdisplayname}<td><a href="{$item->verifierprofileurl}">{$item->verifierdisplayname}</a></td>{/if}
    {if $columns.verifierusername}<td><a href="{$item->verifierprofileurl}">{$item->verifierusername}</a></td>{/if}
    {if $columns.verifierstudentid}<td>{$item->verifierstudentid}</td>{/if}
    {if $columns.verifieremail}<td>{$item->verifieremail}</td>{/if}
    {if $columns.accessfromdate}<td>{$item->accessfromdate}</td>{/if}
    {if $columns.accessrevokedbyauthordate}<td>{$item->accessrevokedbyauthordate}</td>{/if}
    {if $columns.accessrevokedbyaccessordate}<td>{$item->accessrevokedbyaccessordate}</td>{/if}
    {if $columns.verifiedprimarystatmentdate}<td>{$item->verifiedprimarystatmentdate}</td>{/if}
    {if $columns.completionpercentage}<td>{$item->completionpercentage}%</td>{/if}
  </tr>
{/foreach}
