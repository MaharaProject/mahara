<table>
{if $address}
    <tr>
        <td>{str tag='address' section='artefact.internal'}</td>
        <td>{$address}</td>
    </tr>
{/if}
{if $town}
    <tr>
        <td>{str tag='town' section='artefact.internal'}</td>
        <td>{$town}</td>
    </tr>
{/if}
{if $city}
    <tr>
        <td>{str tag='city' section='artefact.internal'}</td>
        <td>{$city}</td>
    </tr>
{/if}
{if $country}
    <tr>
        <td>{str tag='country' section='artefact.internal'}</td>
        <td>{$country}</td>
    </tr>
{/if}
{if $faxnumber}
    <tr>
        <td>{str tag='faxnumber' section='artefact.internal'}</td>
        <td>{$faxnumber}</td>
    </tr>
{/if}
{if $businessnumber}
    <tr>
        <td>{str tag='businessnumber' section='artefact.internal'}</td>
        <td>{$businessnumber}</td>
    </tr>
{/if}
{if $homenumber}
    <tr>
        <td>{str tag='homenumber' section='artefact.internal'}</td>
        <td>{$homenumber}</td>
    </tr>
{/if}
{if $mobilenumber}
    <tr>
        <td>{str tag='mobilenumber' section='artefact.internal'}</td>
        <td>{$mobilenumber}</td>
    </tr>
{/if}
</table>
