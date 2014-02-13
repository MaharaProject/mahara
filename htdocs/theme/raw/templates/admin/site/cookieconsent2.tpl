{include file="header.tpl"}
            <p>{$modifications|safe}</p>

            {foreach from=$data item=item}
            <h3>{$item.title}</h3>
            <ol>
            <li>{$item.item1|safe}</li>
            <li>{$item.item2|safe}</li>
            <li>{$item.item3|safe}</li>
            </ol>
            <table class="cb attachments fullwidth">
              <thead class="expandable-head">
                <tr>
                  <td><a class="toggle" href="#">{str tag=example section=cookieconsent}</a></td>
                </tr>
              </thead>
              <tbody class="expandable-body">
                <tr class="r1">
                  <td><p>{str tag=examplebefore section=cookieconsent}</p>
                </tr>
                <tr class="r0">
                  <td>{$item.code1|safe}</td>
                </tr>
                <tr class="r1">
                  <td><p>{str tag=exampleafter section=cookieconsent}</p></td>
                </tr>
                <tr class="r0">
                  <td>{$item.code2|safe}</td>
                </tr>
              </tbody>
            </table>
            <table class="cb attachments fullwidth">
              <thead class="expandable-head">
                <tr>
                  <td><a class="toggle" href="#">{str tag=itdidntwork section=cookieconsent}</a></td>
                </tr>
              </thead>
              <tbody class="expandable-body">
                <tr class="r1">
                  <td>
                    <p>{$item.help1|safe}</p>
                    <p>{$item.help2|safe}</p>
                  </td>
                </tr>
              </tbody>
            </table>
            {/foreach}

{include file="footer.tpl"}

