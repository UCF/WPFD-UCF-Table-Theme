/**
 * Wpfd
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 * @package WP File Download
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// =require 'jquery.mediaTable.js'

jQuery(document).ready(($) => {


  const table_tree = $('.wpfd-foldertree-ucftable');
  let table_hash = window.location.hash;
  const table_root_cat = $('.wpfd-content-ucftable').data('category');
  const table_cParents = {};
  $('.wpfd-content-ucftable').each(function () {
    const table_topCat = $(this).data('category');
    table_cParents[table_topCat] = {
      parent: 0,
      term_id: table_topCat,
      name: $(this).find('h2').text()
    };
    $(this).find('.wpfdcategory.catlink').each(function () {
      const tempidCat = $(this).data('idcat');
      table_cParents[tempidCat] = {
        parent: table_topCat,
        term_id: tempidCat,
        name: $(this).text()
      };
    });
    initInputSelected(table_topCat);
    initDownloadSelected(table_topCat);
  });

  // load media tables
  $('.wpfd-content .mediaTable').mediaTable();

  Handlebars.registerHelper('bytesToSize', (bytes) => {
    return bytes === 'n/a' ? bytes : bytesToSize(parseInt(bytes));
  });

  function table_initClick() {
    $('.wpfd-content-ucftable .catlink').unbind('click').click(function (e) {
      e.preventDefault();
      table_load($(this).parents('.wpfd-content-ucftable').data('category'), $(this).data('idcat'));
    });
  }

  function initInputSelected(sc) {
    $(document).on('change', `.wpfd-content-ucftable.wpfd-content-multi[data-category=${sc}] input.cbox_file_download`, () => {
      const rootCat = `.wpfd-content-ucftable.wpfd-content-multi[data-category=${sc}]`;
      const selectedFiles = $(`${rootCat} input.cbox_file_download:checked`);
      const filesId = [];
      if (selectedFiles.length) {
        selectedFiles.each((index, file) => {
          filesId.push($(file).data('id'));
        });
      }
      if (filesId.length > 0) {
        $(`${rootCat} .wpfdSelectedFiles`).remove();
        $(`<input type="hidden" class="wpfdSelectedFiles" value="${filesId.join(',')}" />`)
          .insertAfter($(rootCat).find(' #current_category_slug'));
        hideDownloadAllBtn(sc, true);
        $(`${rootCat} .table-download-selected`).remove();
        const downloadSelectedBtn = $(`<a href="javascript:void(0);" class="table-download-selected" style="display: block;">${wpfdparams.translates.download_selected}<i class="zmdi zmdi-check-all wpfd-download-category"></i></a>`);
        downloadSelectedBtn.insertAfter($(rootCat).find('#current_category_slug'));
      } else {
        $(`${rootCat} .wpfdSelectedFiles`).remove();
        $(`${rootCat} .table-download-selected`).remove();
        hideDownloadAllBtn(sc, false);
      }
    });
  }

  function hideDownloadAllBtn(sc, hide) {
    const rootCat = `.wpfd-content-ucftable.wpfd-content-multi[data-category=${sc}]`;
    const downloadCatButton = $(`${rootCat} .table-download-category`);
    if (downloadCatButton.length === 0 || downloadCatButton.hasClass('display-download-category')) {
      return;
    }
    if (hide) {
      $(`${rootCat} .table-download-category`).hide();
    } else {
      $(`${rootCat} .table-download-category`).show();
    }
  }

  function initDownloadSelected(sc) {
    const rootCat = `.wpfd-content-ucftable.wpfd-content-multi[data-category=${sc}]`;
    $(document).on('click', `${rootCat} .table-download-selected`, () => {
      if ($(rootCat).find('.wpfdSelectedFiles').length > 0) {
        const current_category = $(rootCat).find('#current_category').val();
        const category_name = $(rootCat).find('#current_category_slug').val();
        const selectedFilesId = $(rootCat).find('.wpfdSelectedFiles').val();
        $.ajax({
          url: `${wpfdparams.wpfdajaxurl}?action=wpfd&task=files.zipSeletedFiles&filesId=${selectedFilesId}&wpfd_category_id=${current_category}`,
          dataType: 'json'
        }).done((results) => {
          if (results.success) {
            const hash = results.data.hash;
            window.location.href = `${wpfdparams.wpfdajaxurl}?action=wpfd&task=files.downloadZipedFile&hash=${hash}&wpfd_category_id=${current_category}&wpfd_category_name=${category_name}`;
          } else {
            alert(results.data.message);
          }
        });
      }
    });
  }
  table_initClick();

  table_hash = table_hash.replace('#', '');
  if (table_hash !== '') {
    const hasha = table_hash.split('-');
    const re = new RegExp('^(p[0-9]+)$');
    let page = null;
    const stringpage = hasha.pop();

    if (re.test(stringpage)) {
      page = stringpage.replace('p', '');
    }
    var hash_category_id = hasha[0];
    if (!parseInt(hash_category_id)) {
      // todo
    } else {
      setTimeout(() => {
        table_load($('.wpfd-content-ucftable').data('category'), hash_category_id, page);
      }, 100);
    }
  }


  function table_load(sourcecat, catid, page) {
    const pathname = window.location.href.replace(window.location.hash, '');
    const container = $(`.wpfd-content-ucftable.wpfd-content-multi[data-category=${sourcecat}]`);
    container.find('#current_category').val(catid);
    container.next('.wpfd-pagination').remove();

    $(`.wpfd-content-multi[data-category=${sourcecat}] table tbody`).empty();
    $(`.wpfd-content-multi[data-category=${sourcecat}] table`).after($('#wpfd-loading-wrap').html());
    $(`.wpfd-content-multi[data-category=${sourcecat}] .wpfd-categories`).empty();
    // Get categories
    $.ajax({
      url: `${wpfdparams.wpfdajaxurl}task=categories.display&view=categories&id=${catid}&top=${sourcecat}`,
      dataType: 'json'
    }).done((categories) => {

      if (page !== null && page !== undefined) {
        window.history.pushState('', document.title, `${pathname}#${catid}-${categories.category.slug}-p${page}`);
      } else {
        window.history.pushState('', document.title, `${pathname}#${catid}-${categories.category.slug}`);
      }

      container.find('#current_category_slug').val(categories.category.slug);
      const tpltable_sourcecategories = container.parents().find(`#wpfd-template-ucftable-categories-${sourcecat}`).html();
      const template = Handlebars.compile(tpltable_sourcecategories);
      const html = template(categories);
      $(`.wpfd-content-multi[data-category=${sourcecat}] .wpfd-categories`).prepend(html);

      if (table_tree.length) {
        const currentTree = container.find('.wpfd-foldertree-ucftable');
        currentTree.find('li').removeClass('selected');
        currentTree.find('i.md').removeClass('md-folder-open').addClass('md-folder');

        currentTree.jaofiletree('open', catid, currentTree);

        const el = currentTree.find(`a[data-file="${catid}"]`).parent();
        el.find(' > i.md').removeClass('md-folder').addClass('md-folder-open');

        if (!el.hasClass('selected')) {
          el.addClass('selected');
        }

        const ps = currentTree.find('.icon-open-close');

        $.each(ps.get().reverse(), (i, p) => {
          if (typeof $(p).data() !== 'undefined' && $(p).data('id') == Number(hash_category_id)) {
            hash_category_id = $(p).data('parent_id');
            $(p).click();
          }
        });
      }

      // Get files
      $.ajax({
        url: `${wpfdparams.wpfdajaxurl}task=files.display&view=files&id=${catid}&rootcat=${table_root_cat}&page=${page}`,
        dataType: 'json'
      }).done((content) => {
        // $.extend(content,categories);

        if (content.files.length) {
          container.find('.table-download-category').removeClass('display-download-category');
        } else {
          container.find('.table-download-category').addClass('display-download-category');
        }
        $(`.wpfd-content-multi[data-category=${sourcecat}]`).after(content.pagination);
        delete content.pagination;

        const tpltable_source = container.parents().find(`#wpfd-template-ucftable-${sourcecat}`).html();
        const template_table = Handlebars.compile(tpltable_source);
        const html = template_table(content);
        // html = $('<textarea/>').html(html).val();
        $(`.wpfd-content-multi[data-category=${sourcecat}] table tbody`).append(html);
        $(`.wpfd-content-multi[data-category=${sourcecat}] table tbody`).trigger('change');
        $(`.wpfd-content-multi[data-category=${sourcecat}] .mediaTableMenu`).find('input').trigger('change');

        for (let i = 0; i < categories.categories.length; i++) {
          table_cParents[categories.categories[i].term_id] = categories.categories[i];
        }

        table_breadcrum(sourcecat, catid, categories.category);

        table_initClick();
        if (typeof wpfdColorboxInit !== 'undefined') {
          wpfdColorboxInit();
        }
        wpfdTrackDownload();

        table_init_pagination($('.wpfd-pagination'));
        wpfd_remove_loading($('.wpfd-content-multi'));
        $(`.wpfd-content-ucftable.wpfd-content-multi[data-category=${sourcecat}] .wpfdSelectedFiles`).remove();
        $(`.wpfd-content-ucftable.wpfd-content-multi[data-category=${sourcecat}] .table-download-selected`).remove();
        hideDownloadAllBtn(sourcecat, false);
      });

    });
  }

  function table_breadcrum(sourcecat, catid, category) {
    const links = [];
    let current_Cat = table_cParents[catid];
    if (!current_Cat) {
      $(`.wpfd-content-ucftable[data-category=${sourcecat}] .table-download-category`).attr('href', category.linkdownload_cat);
      return false;
    }
    links.unshift(current_Cat);

    if (current_Cat.parent !== 0) {
      while (table_cParents[current_Cat.parent]) {
        current_Cat = table_cParents[current_Cat.parent];
        links.unshift(current_Cat);
      }
    }

    let html = '';
    for (let i = 0; i < links.length; i++) {
      if (i < links.length - 1) {
        html += `<li><a class="catlink" data-idcat="${links[i].term_id}" href="javascript:void(0)">${links[i].name}</a><span class="divider"> &gt; </span></li>`;
      } else {
        html += `<li><span>${links[i].name}</span></li>`;
      }
    }
    $(`.wpfd-content-ucftable[data-category=${sourcecat}] .wpfd-breadcrumbs-ucftable li`).remove();
    $(`.wpfd-content-ucftable[data-category=${sourcecat}] .wpfd-breadcrumbs-ucftable`).append(html);
    $(`.wpfd-content-ucftable[data-category=${sourcecat}] .table-download-category`).attr('href', category.linkdownload_cat);
  }

  if (table_tree.length) {
    table_tree.each(function () {
      const table_topCat = $(this).parents('.wpfd-content-ucftable.wpfd-content-multi').data('category');
      $(this).jaofiletree({
        script: `${wpfdparams.wpfdajaxurl}task=categories.getCats`,
        usecheckboxes: false,
        root: table_topCat,
        showroot: table_cParents[table_topCat].name,
        onclick: function (elem, file) {

          const table_topCat = $(elem).parents('.wpfd-content-ucftable.wpfd-content-multi').data('category');
          if (table_topCat !== file) {

            $(elem).parents('.directory').each(function () {
              const $this = $(this);
              const category = $this.find(' > a');
              const parent = $this.find('.icon-open-close');
              if (parent.length > 0) {
                if (typeof table_cParents[category.data('file')] === 'undefined') {
                  table_cParents[category.data('file')] = {
                    parent: parent.data('parent_id'),
                    term_id: category.data('file'),
                    name: category.text()
                  };
                }
              }
            });

          }

          table_load(table_topCat, file);
        }
      });
    });
  }


  $('.wpfd-pagination').each(function () {
    const $this = $(this);
    table_init_pagination($this);
  });

  function table_init_pagination($this) {

    const number = $this.find('a:not(.current)');

    const wrap = $this.prev('.wpfd-content-ucftable');

    const current_category = wrap.find('#current_category').val();
    const sourcecat = wrap.data('category');

    number.unbind('click').bind('click', function () {
      const page_number = $(this).attr('data-page');

      if (typeof page_number !== 'undefined') {
        const pathname = window.location.href.replace(window.location.hash, '');
        const category = $(`.wpfd-content-multi[data-category=${sourcecat}]`).find('#current_category').val();
        const category_slug = $(`.wpfd-content-multi[data-category=${sourcecat}]`).find('#current_category_slug').val();

        window.history.pushState('', document.title, `${pathname}#${category}-${category_slug}-p${page_number}`);

        $(`.wpfd-content-multi[data-category=${sourcecat}] table tbody tr:not(.topheader)`).remove();
        $(`.wpfd-content-multi[data-category=${sourcecat}] table`).after($('#wpfd-loading-wrap').html());
        // Get files
        $.ajax({
          url: `${wpfdparams.wpfdajaxurl}task=files.display&view=files&id=${current_category}&rootcat=${sourcecat}&page=${page_number}`,
          dataType: 'json',
          beforeSend: function () {
            $('html, body').animate({
              scrollTop: $('.wpfd-content-ucftable').offset().top
            }, 'fast');
          }
        }).done((content) => {

          delete content.category;
          wrap.next('.wpfd-pagination').remove();
          wrap.after(content.pagination);
          delete content.pagination;
          const tpltable_source = wrap.parents().find(`#wpfd-template-ucftable-${sourcecat}`).html();
          const template_table = Handlebars.compile(tpltable_source);
          const html = template_table(content);
          $(`.wpfd-content-multi[data-category=${sourcecat}] table tbody`).append(html);
          $(`.wpfd-content-multi[data-category=${sourcecat}] table tbody`).trigger('change');
          $(`.wpfd-content-multi[data-category=${sourcecat}] .mediaTableMenu`).find('input').trigger('change');

          if (typeof wpfdColorboxInit !== 'undefined') {
            wpfdColorboxInit();
          }
          table_init_pagination(wrap.next('.wpfd-pagination'));
          wpfd_remove_loading($('.wpfd-content-multi'));
        });
      }

    });
  }
});
