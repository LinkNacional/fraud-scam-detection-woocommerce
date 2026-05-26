jQuery(document).ready(function ($) {
    // ── Nav carousel (setup first so scrollTabIntoView can reference $clip) ──
    var $outer = $('.admin-layout-top-menu-outer');
    var $clip  = $outer.find('.admin-layout-top-menu-clip');
    var $nav   = $clip.find('.admin-layout-top-menu');
    var $prev  = $outer.find('.admin-layout-nav-arrow--prev');
    var $next  = $outer.find('.admin-layout-nav-arrow--next');
    var offset = 0;

    function getMaxOffset() {
        return Math.max(0, $nav[0].scrollWidth - $clip[0].clientWidth);
    }

    function applyOffset(newOffset) {
        var max = getMaxOffset();
        offset = Math.max(0, Math.min(newOffset, max));
        $nav[0].style.transform = 'translateX(-' + offset + 'px)';
        updateArrows();
    }

    function updateArrows() {
        var overflows = getMaxOffset() > 0;
        $outer.toggleClass('has-overflow', overflows);
        if (overflows) {
            $prev.prop('disabled', offset <= 0);
            $next.prop('disabled', offset >= getMaxOffset() - 1);
        }
    }

    // Brings the nav link for tabId into the visible clip area
    function scrollTabIntoView(tabId) {
        var $tab = $('#nav-' + tabId);
        if (!$tab.length) return;
        var tabLeft  = $tab[0].offsetLeft;
        var tabRight = tabLeft + $tab[0].offsetWidth;
        var clipW    = $clip[0].clientWidth;
        if (tabLeft < offset) {
            applyOffset(tabLeft);
        } else if (tabRight > offset + clipW) {
            applyOffset(tabRight - clipW);
        }
    }

    $prev.on('click', function () {
        applyOffset(offset - Math.round($clip[0].clientWidth * 0.6));
    });

    $next.on('click', function () {
        applyOffset(offset + Math.round($clip[0].clientWidth * 0.6));
    });

    $(window).on('resize', function () {
        applyOffset(offset); // clamp offset if viewport widened
    });
    updateArrows();

    // ── Tab switching ─────────────────────────────────────────────────────
    function switchBlock(tabId) {
        var $link = $('#nav-' + tabId);
        if (!$link.length) return;
        $('.admin-layout-title-link').removeClass('active');
        $link.addClass('active');
        $('.admin-layout-block').removeClass('active');
        $('#block-' + tabId).addClass('active');
    }

    function activateTab(tabId) {
        scrollTabIntoView(tabId);
        switchBlock(tabId);
    }

    $('.admin-layout-title-link').on('click', function (e) {
        e.preventDefault();
        activateTab($(this).attr('id').replace('nav-', ''));
    });

    // Links with data-goto-tab="<tabId>": wait for the carousel transition
    // (300 ms) to finish before switching the block, so the nav slides
    // into view before the content changes.
    $(document).on('click', '[data-goto-tab]', function (e) {
        e.preventDefault();
        var tabId = $(this).data('goto-tab');
        scrollTabIntoView(tabId);
        setTimeout(function () {
            switchBlock(tabId);
        }, 320); // slightly above the 300ms CSS transition
    });

    // Activate initial tab (marked active in PHP)
    var $firstActive = $('.admin-layout-title-link.active');
    if ($firstActive.length) {
        activateTab($firstActive.attr('id').replace('nav-', ''));
    }
});