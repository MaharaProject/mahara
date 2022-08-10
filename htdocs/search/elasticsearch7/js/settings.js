/**
 * Supporting code for the Elasticsearch 7 settings page.
 *
 * @source: git.mahara.org
 *
 * @licstart
 * Copyright (C) 2020  Catalyst IT Limited
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

let es7typekey = '';

function es7requeueitems(type) {
  es7typekey = type.replaceAll('_','');

  const ids = $('#es7-requeue-' + es7typekey).val();
  // Make the call to enqueue the items for this type.
  const data = {
    "type": type,
    "ids": ids
  }
  sendjsonrequest(
    config.wwwroot + 'search/elasticsearch7/json/enqueue.php',
    data,
    'POST',
    function (data) {
      console.log(data);
      console.log(es7typekey);
      if (data.error != undefined) {
        alert(data.error);
        $('#es7-requeue-' + es7typekey).addClass('is-invalid');
      }
      else {
        $('#es7-requeue-' + es7typekey)
          .removeClass('is-invalid')
          .val('');
        $('#es7-queue-count-' + es7typekey).text(data.queue);
        $('#es7-index-count-' + es7typekey).text(data.index);
      }
    }
  );
}