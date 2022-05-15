/**
 * Handle the release of submissions table and form.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

 // TODO: very similar to user search, potentially could be reused
var CurrentSubmissionsRelease = (function($) {
  return function(pager) {
      var self = this;

      this.init = function() {
          self.selectcontentrelease = {};

          self.rewriteHeaders();
          self.rewriteQueryButton();
          self.rewriteCheckboxes();

          paginatorProxy.addObserver(self);
          var oldparams = $.extend({}, pager.params);
          $(self).on('pagechanged', function(e, data) {
            if (!data.changedPage) {
                self.selectcontentrelease = {};
            }
            oldparams = $.extend({}, pager.params);
            $.each(['releaseform'], self.connectSelectedContentForm);
            self.rewriteCheckboxes();
          });
          $.each(['releaseform'], self.connectSelectedContentForm);
      };

      this.rewriteHeaders = function() {
          $('#searchresults th.search-results-sort-column a').each(function() {
              var href = $(this).attr('href');
              var sortby = getUrlParameter('sortby', href);
              var sortdir = getUrlParameter('sortdir', href);

              $(this).on("click", function() {
                  var header = $(this).parent();
                  if (!(header.hasClass('asc') || header.hasClass('desc'))) {
                      sortdir = 'asc';
                  }

                  pager.params.sortby = sortby;
                  pager.params.sortdir = sortdir;

                  $(this).closest('thead').find('th').removeClass('asc').removeClass('desc');
                  $(this).parent().addClass(sortdir);
                  var re1 = new RegExp(strings.descending);
                  var re2 = new RegExp(strings.ascending);
                  $(this).closest('tr').find('span').each(function(i, el) {
                      el.innerHTML = el.innerHTML.replace(re1, strings.ascending);
                  });
                  if (sortdir == 'asc') {
                      $(this).find('span').html($(this).find('span').html().replace(re2, strings.descending));
                  }
                  sortdir = (sortdir == 'desc') ? 'asc' : 'desc';

                  pager.sendQuery();
                  return false;
              });
          });
      };

      this.rewriteQueryButton = function() {
          $('#query-button').on("click", function() {
              pager.params.query = $('#query').val();
              var institution = $('#institution');
              if (institution.length) {
                  pager.params.institution = institution.val();
              }
              var institution_requested = $('#institution_requested');
              if (institution_requested.length) {
                  pager.params.institution_requested = institution_requested.val();
              }
              pager.sendQuery();
              return false;
          });
      };

      this.rewriteCheckboxes = function() {
          // For the release checkboxes.
          console.log($('#searchresults input.selectcontentrelease').length);
          $('#searchresults input.selectcontentrelease').each(function() {
              var value = $(this).val();
              var releasetype = $(this).data('releasetype');
              $(this).on('change', function() {
                  if ($(this).prop('checked')) {
                      self.selectcontentrelease[value] = releasetype;
                  }
                  else {
                      delete self.selectcontentrelease[value];
                  }
              });
              if (self.selectcontentrelease[value]) {
                  $(this).prop('checked', true);
              }
          });
          if ($('#selectallrelease').length) {
              $('#selectallrelease').on("click", function() {
                  $('#searchresults input.selectcontentrelease').each(function() {
                      self.selectcontentrelease[$(this).val()] = $(this).data('releasetype');
                      $(this).prop('checked', true);
                  });
                  return false;
              });
              $('#selectnonerelease').on("click", function() {
                  $('#searchresults input.selectcontentrelease').each(function() {
                      delete self.selectcontentrelease[$(this).val()];
                      $(this).prop('checked', false);
                  });
                  return false;
              });
          }
      };

      this.connectSelectedContentForm = function(i, formid) {
          $('#' + formid + ' input.button').on("click", function() {
              var countrelease = 0;
              if (self.selectcontentrelease) {
                  for (var j in self.selectcontentrelease) {
                      $('#' + formid).append($('<input>', {
                          'type': 'checkbox',
                          'name': 'releaseids[' + j + ']',
                          'value': self.selectcontentrelease[j],
                          'class': 'd-none',
                          'checked': 'checked'
                      }));
                      countrelease++;
                  }
              }

              if ((countrelease && $(this).attr('name') == 'releasesubmissions')) {
                  $('#nocontentselected').addClass('d-none');
                  $('#' + formid).append($('<input>', {
                      'type': 'hidden',
                      'name': 'action',
                      'value': $(this).attr('name')
                  }));
                  $('#' + formid).trigger('submit');
                  return false;
              }
              $('#nocontentselected').removeClass('d-none');
              return false;
          });
      };

      this.init();
  };
}(jQuery));
