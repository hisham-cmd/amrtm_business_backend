/**
 * Icon Picker — searchable Tabler icon grid.
 * openIconPicker(inputEl) sets the ti-* class name on the target input.
 */
(function () {
  'use strict';

  var modal       = null;
  var inputTarget = null;

  /* ~400 curated Tabler icon names */
  var ICONS = [
    /* ── Buildings & places ── */
    'ti-building','ti-building-bank','ti-building-castle','ti-building-church',
    'ti-building-community','ti-building-factory','ti-building-hospital',
    'ti-building-lighthouse','ti-building-monument','ti-building-pavilion',
    'ti-building-skyscraper','ti-building-store','ti-building-warehouse',
    'ti-home','ti-home-2','ti-school','ti-university','ti-city',
    /* ── Government & law ── */
    'ti-shield','ti-shield-check','ti-shield-lock','ti-gavel','ti-scale',
    'ti-badge','ti-certificate','ti-license','ti-id-badge','ti-id-badge-2',
    'ti-passport','ti-writing','ti-file-certificate','ti-stamp',
    /* ── Documents ── */
    'ti-file','ti-file-text','ti-file-check','ti-file-invoice','ti-file-search',
    'ti-file-description','ti-file-analytics','ti-file-dollar','ti-file-export',
    'ti-file-import','ti-file-minus','ti-file-plus','ti-file-report',
    'ti-file-zip','ti-clipboard','ti-clipboard-check','ti-clipboard-list',
    'ti-notes','ti-note','ti-book','ti-book-2','ti-books','ti-bookmarks',
    /* ── People & users ── */
    'ti-user','ti-user-check','ti-user-circle','ti-user-plus','ti-users',
    'ti-user-badge','ti-user-bolt','ti-user-cog','ti-user-dollar',
    'ti-user-search','ti-user-star','ti-man','ti-woman','ti-baby-carriage',
    'ti-friends','ti-user-tie','ti-soldier','ti-nurse',
    /* ── Finance & commerce ── */
    'ti-cash','ti-coin','ti-coins','ti-credit-card','ti-wallet',
    'ti-receipt','ti-receipt-2','ti-currency-dollar','ti-currency-riyal',
    'ti-shopping-cart','ti-shopping-bag','ti-package','ti-barcode',
    'ti-chart-bar','ti-chart-line','ti-chart-pie','ti-chart-dots',
    'ti-trending-up','ti-trending-down','ti-report-money',
    /* ── Health & medical ── */
    'ti-heart','ti-heart-rate','ti-stethoscope','ti-pill','ti-vaccine',
    'ti-first-aid-kit','ti-ambulance','ti-wheelchair','ti-virus',
    'ti-dental','ti-microscope','ti-dna',
    /* ── Education ── */
    'ti-pencil','ti-pencil-alt','ti-pen','ti-backpack','ti-chalkboard',
    'ti-abacus','ti-atom','ti-math','ti-flask','ti-test-pipe',
    /* ── Tech & devices ── */
    'ti-device-laptop','ti-device-mobile','ti-device-tablet',
    'ti-device-desktop','ti-device-tv','ti-printer','ti-scan',
    'ti-wifi','ti-bluetooth','ti-signal-4g','ti-cpu',
    'ti-server','ti-database','ti-cloud','ti-cloud-upload','ti-cloud-download',
    'ti-code','ti-terminal','ti-api','ti-bug',
    /* ── Communication ── */
    'ti-mail','ti-mail-opened','ti-send','ti-message','ti-message-2',
    'ti-messages','ti-phone','ti-phone-call','ti-bell','ti-bell-ringing',
    'ti-speakerphone','ti-broadcast','ti-antenna',
    /* ── Transport ── */
    'ti-car','ti-truck','ti-bus','ti-plane','ti-ship','ti-train',
    'ti-bike','ti-motorbike','ti-ambulance','ti-firetruck',
    'ti-map','ti-map-pin','ti-location','ti-navigation','ti-compass',
    /* ── Nature & environment ── */
    'ti-leaf','ti-tree','ti-plant','ti-flower','ti-sun','ti-moon',
    'ti-cloud-rain','ti-snowflake','ti-droplet','ti-wave','ti-mountain',
    'ti-recycle','ti-eco','ti-solar-panel',
    /* ── Food & services ── */
    'ti-tool','ti-tools','ti-hammer','ti-wrench','ti-drill','ti-paint',
    'ti-brush','ti-bucket','ti-home-wrench','ti-stairs',
    'ti-coffee','ti-bowl','ti-fork-knife','ti-bottle',
    /* ── Media & content ── */
    'ti-photo','ti-camera','ti-video','ti-microphone','ti-headphones',
    'ti-music','ti-player-play','ti-movie','ti-news',
    /* ── Actions & UI ── */
    'ti-search','ti-filter','ti-sort','ti-refresh','ti-settings',
    'ti-adjustments','ti-lock','ti-lock-open','ti-key','ti-eye',
    'ti-eye-off','ti-edit','ti-trash','ti-plus','ti-minus','ti-check',
    'ti-x','ti-arrow-right','ti-arrow-left','ti-arrow-up','ti-arrow-down',
    'ti-chevron-right','ti-chevron-left','ti-external-link','ti-link',
    'ti-share','ti-download','ti-upload','ti-copy','ti-cut','ti-clipboard-copy',
    'ti-star','ti-heart','ti-bookmark','ti-flag','ti-tag','ti-tags',
    /* ── Symbols & misc ── */
    'ti-info-circle','ti-alert-circle','ti-alert-triangle','ti-help-circle',
    'ti-circle-check','ti-circle-x','ti-ban','ti-forbid',
    'ti-world','ti-globe','ti-language','ti-translate',
    'ti-clock','ti-calendar','ti-calendar-event','ti-history',
    'ti-grid','ti-list','ti-layout','ti-layout-dashboard','ti-menu-2',
    'ti-dots','ti-dots-vertical','ti-grip','ti-move',
    'ti-anchor','ti-atom-2','ti-award','ti-bulb','ti-target',
    'ti-rocket','ti-drone','ti-satellite','ti-telescope','ti-infinity',
    'ti-percentage','ti-hash','ti-at','ti-qrcode','ti-fingerprint',
    'ti-face-id','ti-ai','ti-robot','ti-sparkles'
  ];

  function buildModal() {
    modal = document.createElement('div');
    modal.id = 'icon-picker-overlay';
    modal.style.cssText =
      'position:fixed;inset:0;z-index:99999;background:rgba(10,14,60,.55);' +
      'display:none;align-items:center;justify-content:center;' +
      'backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);';

    modal.innerHTML =
      '<div style="background:#fff;border-radius:18px;width:min(680px,96vw);' +
      'max-height:82vh;display:flex;flex-direction:column;overflow:hidden;' +
      'box-shadow:0 24px 64px rgba(10,14,60,.28);">' +

        /* header */
        '<div style="display:flex;align-items:center;gap:10px;padding:1rem 1.3rem;' +
        'border-bottom:1px solid rgba(26,35,126,.09);flex-shrink:0;">' +
          '<span style="font-size:15px;font-weight:800;color:#0D1257;flex:1;">اختر أيقونة</span>' +
          '<div style="position:relative;">' +
            '<input id="ip-search" type="text" placeholder="ابحث..." autocomplete="off" ' +
            'style="width:200px;height:36px;padding:0 36px 0 12px;border-radius:9px;' +
            'border:1.5px solid rgba(26,35,126,.15);font-size:13px;outline:none;' +
            'font-family:inherit;color:#0D1257;direction:rtl;"/>' +
            '<i class="ti ti-search" style="position:absolute;right:10px;top:50%;' +
            'transform:translateY(-50%);color:#7A82B8;font-size:15px;pointer-events:none;"></i>' +
          '</div>' +
          '<button id="ip-close" type="button" ' +
          'style="width:32px;height:32px;border-radius:8px;border:1.5px solid rgba(26,35,126,.12);' +
          'background:transparent;color:#7A82B8;font-size:19px;cursor:pointer;line-height:1;' +
          'display:flex;align-items:center;justify-content:center;">&times;</button>' +
        '</div>' +

        /* count */
        '<div id="ip-count" style="padding:.4rem 1.3rem;font-size:11.5px;color:#7A82B8;' +
        'font-weight:700;flex-shrink:0;border-bottom:1px solid rgba(26,35,126,.05);">' +
          ICONS.length + ' أيقونة' +
        '</div>' +

        /* grid */
        '<div id="ip-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));' +
        'gap:.45rem;padding:.9rem 1.3rem 1.3rem;overflow-y:auto;flex:1;"></div>' +

      '</div>';

    document.body.appendChild(modal);

    modal.addEventListener('click', function (e) {
      if (e.target === modal) closeModal();
    });
    document.getElementById('ip-close').addEventListener('click', closeModal);
    document.getElementById('ip-search').addEventListener('input', function () {
      renderGrid(this.value.toLowerCase().trim());
    });

    renderGrid('');
  }

  function renderGrid(q) {
    var grid     = document.getElementById('ip-grid');
    var countEl  = document.getElementById('ip-count');
    var filtered = q ? ICONS.filter(function (n) { return n.includes(q); }) : ICONS;

    countEl.textContent = filtered.length + ' أيقونة';

    if (!filtered.length) {
      grid.innerHTML =
        '<div style="grid-column:1/-1;text-align:center;padding:3rem;color:#7A82B8;font-size:13px;">لا توجد نتائج</div>';
      return;
    }

    grid.innerHTML = filtered.map(function (n) {
      var label = n.replace('ti-', '');
      return (
        '<div onclick="window.__ipPick(\'' + n + '\')" ' +
        'style="background:#F4F6FB;border-radius:10px;border:1.5px solid rgba(26,35,126,.08);' +
        'padding:.6rem .3rem .5rem;display:flex;flex-direction:column;align-items:center;' +
        'gap:.35rem;cursor:pointer;transition:border-color .15s,background .15s;" ' +
        'title="' + n + '" ' +
        'onmouseover="this.style.borderColor=\'#1A237E\';this.style.background=\'rgba(26,35,126,.07)\'" ' +
        'onmouseout="this.style.borderColor=\'rgba(26,35,126,.08)\';this.style.background=\'#F4F6FB\'">' +
          '<i class="ti ' + n + '" style="font-size:22px;color:#3A4490;"></i>' +
          '<span style="font-size:9px;color:#7A82B8;text-align:center;word-break:break-all;' +
          'line-height:1.3;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' +
            label +
          '</span>' +
        '</div>'
      );
    }).join('');
  }

  window.__ipPick = function (iconName) {
    if (inputTarget) {
      inputTarget.value = iconName;
      inputTarget.dispatchEvent(new Event('input', { bubbles: true }));
    }
    closeModal();
  };

  function closeModal() {
    if (!modal) return;
    modal.style.display = 'none';
    var s = document.getElementById('ip-search');
    if (s) s.value = '';
    renderGrid('');
  }

  window.openIconPicker = function (inputEl) {
    inputTarget = inputEl || null;
    if (!modal) buildModal();
    modal.style.display = 'flex';
    setTimeout(function () {
      var s = document.getElementById('ip-search');
      if (s) s.focus();
    }, 60);
  };
})();
