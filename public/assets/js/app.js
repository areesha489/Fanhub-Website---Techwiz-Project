/* =============================================================================
   FAN HUB+ — front-end runtime (vanilla JS, no framework)
   -------------------------------------------------------------------------
   Public API:  window.FanHub = { toast, csrf, post, get, refreshIcons, ... }
   Every feature degrades gracefully: failures are caught, nothing throws
   uncaught into the console.
   ========================================================================== */
(function () {
  'use strict';

  /* ---------------------------------------------------------------------------
     0. Boot
  ------------------------------------------------------------------------- */
  var doc = document;
  var html = doc.documentElement;

  // Enables image fade-in only when JS is alive (CSS gates on html.js-fade).
  html.classList.add('js-fade');

  var FanHub = (window.FanHub = window.FanHub || {});

  FanHub.csrf =
    (doc.querySelector('meta[name="csrf-token"]') || {}).content ||
    (doc.querySelector('input[name="_token"]') || {}).value ||
    '';

  FanHub.urls = {
    login: (meta('fh-login-url') || '/login'),
    rate: meta('fh-rate-url') || '/rate',
    settings: meta('fh-settings-url') || '/settings',
    chat: meta('fh-chat-url') || '/chat',
    bookmarkBase: meta('fh-bookmark-update-base') || '/bookmarks'
  };

  function meta(name) {
    var el = doc.querySelector('meta[name="' + name + '"]');
    return el ? el.getAttribute('content') : null;
  }

  function $(sel, ctx) {
    return (ctx || doc).querySelector(sel);
  }

  function $$(sel, ctx) {
    return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel));
  }

  function cssEscape(value) {
    if (window.CSS && CSS.escape) return CSS.escape(value);
    return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
  }

  function prefersReducedMotion() {
    return (
      html.getAttribute('data-motion') === 'reduced' ||
      (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)
    );
  }

  /* ---------------------------------------------------------------------------
     1. Tiny helpers
  ------------------------------------------------------------------------- */
  FanHub.escapeHtml = function (value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };

  FanHub.icons = {
    success: 'bi-check-circle-fill',
    error: 'bi-exclamation-octagon-fill',
    info: 'bi-info-circle-fill',
    warning: 'bi-exclamation-triangle-fill'
  };

  /** Re-run icon rendering after injecting markup (web-font icons: no-op safe). */
  FanHub.refreshIcons = function () {
    try {
      if (typeof window.lucide !== 'undefined' && window.lucide.createIcons) {
        window.lucide.createIcons();
      }
      if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Tooltip) {
        $$('[data-bs-toggle="tooltip"]').forEach(function (el) {
          if (!el._tooltip) {
            el._tooltip = new bootstrap.Tooltip(el, { container: el });
          }
        });
      }
    } catch (e) {
      /* noop */
    }
  };

  /* ---------------------------------------------------------------------------
     2. Toasts
  ------------------------------------------------------------------------- */
  function toastHost() {
    var host = $('#fh-toasts');
    if (!host) {
      host = doc.createElement('div');
      host.id = 'fh-toasts';
      host.className = 'fh-toasts';
      host.setAttribute('aria-live', 'polite');
      host.setAttribute('aria-atomic', 'true');
      doc.body.appendChild(host);
    }
    return host;
  }

  /**
   * Show a toast notification.
   * @param {'success'|'error'|'info'|'warning'} kind
   * @param {string} message
   * @param {string} [title]
   */
  FanHub.toast = function (kind, message, title) {
    try {
      kind = ['success', 'error', 'info', 'warning'].indexOf(kind) > -1 ? kind : 'info';
      var host = toastHost();
      var titles = {
        success: 'Success',
        error: 'Something went wrong',
        info: 'Heads up',
        warning: 'Careful'
      };

      var el = doc.createElement('div');
      el.className = 'fh-toast fh-toast-' + kind;
      el.setAttribute('role', 'status');
      el.innerHTML =
        '<span class="fh-toast-icon"><i class="bi ' +
        FanHub.icons[kind] +
        '"></i></span>' +
        '<div class="fh-toast-body">' +
        '<strong class="fh-toast-title">' +
        FanHub.escapeHtml(title || titles[kind]) +
        '</strong>' +
        '<span class="fh-toast-msg">' +
        FanHub.escapeHtml(message) +
        '</span>' +
        '</div>' +
        '<button type="button" class="fh-toast-close" aria-label="Dismiss notification">' +
        '<i class="bi bi-x-lg"></i></button>';

      var remove = function () {
        if (!el.parentNode) return;
        el.classList.add('is-leaving');
        window.setTimeout(function () {
          if (el.parentNode) el.parentNode.removeChild(el);
        }, 260);
      };

      el.querySelector('.fh-toast-close').addEventListener('click', remove);
      host.appendChild(el);
      window.setTimeout(remove, 5200);
      return el;
    } catch (e) {
      return null;
    }
  };

  /* ---------------------------------------------------------------------------
     3. HTTP helpers
  ------------------------------------------------------------------------- */
  function buildUrl(url, data) {
    if (!data) return url;
    var params = new URLSearchParams();
    Object.keys(data).forEach(function (key) {
      if (data[key] === undefined || data[key] === null) return;
      params.append(key, data[key]);
    });
    var qs = params.toString();
    if (!qs) return url;
    return url + (url.indexOf('?') > -1 ? '&' : '?') + qs;
  }

  /**
   * Fetch JSON.
   * @returns {Promise<{ok:boolean,status:number,data:*}>}
   */
  FanHub.request = function (url, data, method) {
    method = (method || 'POST').toUpperCase();
    var init = {
      method: method,
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': FanHub.csrf
      }
    };

    if (method !== 'GET' && method !== 'HEAD') {
      init.headers['Content-Type'] = 'application/json';
      var payload = Object.assign({}, data || {});
      if (FanHub.csrf) payload._token = FanHub.csrf;
      init.body = JSON.stringify(payload);
    } else {
      url = buildUrl(url, data);
    }

    return fetch(url, init)
      .then(function (res) {
        return res
          .json()
          .catch(function () {
            return null;
          })
          .then(function (body) {
            if (res.status === 401) {
              var next = encodeURIComponent(window.location.pathname + window.location.search);
              var login = FanHub.urls.login || '/login';
              window.location.href = login + (login.indexOf('?') > -1 ? '&' : '?') + 'next=' + next;
              return { ok: false, status: 401, data: null };
            }
            return { ok: res.ok, status: res.status, data: body };
          });
      })
      .catch(function () {
        return { ok: false, status: 0, data: null };
      });
  };

  FanHub.post = function (url, data, method) {
    return FanHub.request(url, data, method || 'POST');
  };

  FanHub.get = function (url, data) {
    return FanHub.request(url, data, 'GET');
  };

  /* ---------------------------------------------------------------------------
     4. Flash bridge (data-flash-* from partials/flash.blade.php)
  ------------------------------------------------------------------------- */
  function runFlashBridge() {
    var flash = $('#fh-flash');
    if (!flash) return;
    [
      ['success', 'Success'],
      ['error', 'Error'],
      ['info', 'Notice'],
      ['warning', 'Warning']
    ].forEach(function (pair) {
      var value = flash.getAttribute('data-flash-' + pair[0]);
      if (value) FanHub.toast(pair[0], value, pair[1]);
    });
  }

  /* ---------------------------------------------------------------------------
     5. Image fade-in
  ------------------------------------------------------------------------- */
  function watchImage(img) {
    if (!img || img._fhWatched) return;
    img._fhWatched = true;
    var done = function () {
      img.classList.add('is-loaded');
    };
    if (img.complete && img.naturalWidth !== 0) {
      done();
      return;
    }
    img.addEventListener('load', done, { once: true });
    img.addEventListener('error', done, { once: true });
  }

  function watchImages(root) {
    $$('.fh-img', root).forEach(watchImage);
    if (root && root.classList && root.classList.contains('fh-img')) watchImage(root);
  }

  /* ---------------------------------------------------------------------------
     6. Bookmarks (AJAX) — contract §9.2
  ------------------------------------------------------------------------- */
  function syncBookmarkButtons(type, id, active) {
    var selector =
      '[data-bookmark][data-type="' +
      cssEscape(type) +
      '"][data-id="' +
      cssEscape(String(id)) +
      '"]';
    $$(selector).forEach(function (btn) {
      btn.classList.toggle('is-active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      btn.setAttribute('aria-label', active ? 'Remove bookmark' : 'Bookmark');
      var icon = btn.querySelector('i');
      if (icon) icon.className = 'bi ' + (active ? 'bi-bookmark-fill' : 'bi-bookmark');
    });
  }

  function toggleBookmark(btn) {
    var type = btn.getAttribute('data-type');
    var id = btn.getAttribute('data-id');
    var storeUrl = btn.getAttribute('data-url') || '/bookmarks';
    var updateUrl = btn.getAttribute('data-update') || '';

    if (btn.getAttribute('data-guest') === '1') {
      var login = FanHub.urls.login || '/login';
      var next = encodeURIComponent(window.location.pathname + window.location.search);
      window.location.href = login + (login.indexOf('?') > -1 ? '&' : '?') + 'next=' + next;
      return;
    }

    var wasActive = btn.classList.contains('is-active');
    var nextActive = !wasActive;

    // Optimistic update.
    syncBookmarkButtons(type, id, nextActive);
    btn.classList.add('is-busy');

    var request = nextActive
      ? FanHub.post(storeUrl, { type: type, id: id, note: null })
      : FanHub.post(updateUrl || storeUrl, nextActive ? { type: type, id: id } : {}, 'DELETE');

    request.then(function (res) {
      btn.classList.remove('is-busy');

      if (!res.ok) {
        syncBookmarkButtons(type, id, wasActive);
        FanHub.toast('error', 'We could not save that bookmark. Please try again.');
        return;
      }

      var payload = res.data && res.data.data ? res.data.data : res.data;
      if (nextActive && payload && payload.id) {
        $$(selector).forEach(function (b) {
          b.setAttribute('data-update', (meta('fh-bookmark-update-base') || '/bookmarks') + '/' + payload.id);
          b.setAttribute('data-bookmark-id', String(payload.id));
          var noteBtn = b.querySelector('[data-bookmark-note]');
          if (noteBtn && payload.note !== undefined) {
            noteBtn.setAttribute('data-note', payload.note || '');
          }
        });
      }

      FanHub.toast(
        nextActive ? 'success' : 'info',
        nextActive ? 'Saved to your bookmarks.' : 'Removed from your bookmarks.'
      );

      doc.dispatchEvent(
        new CustomEvent('fanhub:bookmark', {
          detail: { type: type, id: id, bookmarked: nextActive, bookmarkId: payload && payload.id }
        })
      );
    });
  }

  /* ---------------------------------------------------------------------------
     7. Bookmark inline note editing — contract §9.3
  ------------------------------------------------------------------------- */
  function closeNoteEditor() {
    var open = $('.fh-note-editor');
    if (open && open.parentNode) open.parentNode.removeChild(open);
  }

  function openNoteEditor(btn) {
    closeNoteEditor();
    var url = btn.getAttribute('data-update');
    if (!url) {
      FanHub.toast('info', 'Save the bookmark first, then add a note.');
      return;
    }

    var rect = btn.getBoundingClientRect();
    var editor = doc.createElement('div');
    editor.className = 'fh-note-editor';
    editor.setAttribute('role', 'dialog');
    editor.setAttribute('aria-label', 'Edit bookmark note');
    editor.innerHTML =
      '<label class="fh-label" for="fh-note-input">Personal note</label>' +
      '<textarea id="fh-note-input" class="fh-field" rows="3" placeholder="Why does this matter to you?"></textarea>' +
      '<p class="fh-help">Only you can see this note.</p>' +
      '<div class="fh-note-actions">' +
      '<button type="button" class="btn btn-fh-ghost btn-sm" data-note-cancel>Cancel</button>' +
      '<button type="button" class="btn btn-fh btn-sm" data-note-save>Save note</button>' +
      '</div>';

    doc.body.appendChild(editor);

    var top = Math.min(rect.bottom + 8, window.innerHeight - editor.offsetHeight - 12);
    var left = Math.min(Math.max(8, rect.left - 140), window.innerWidth - editor.offsetWidth - 12);
    editor.style.top = Math.max(8, top) + 'px';
    editor.style.left = left + 'px';

    var field = editor.querySelector('textarea');
    field.value = btn.getAttribute('data-note') || '';
    field.focus();

    editor.querySelector('[data-note-cancel]').addEventListener('click', closeNoteEditor);
    editor.querySelector('[data-note-save]').addEventListener('click', function () {
      var saveBtn = this;
      saveBtn.disabled = true;
      FanHub.post(url, { note: field.value }, 'PUT').then(function (res) {
        saveBtn.disabled = false;
        if (!res.ok) {
          FanHub.toast('error', 'The note could not be saved.');
          return;
        }
        btn.setAttribute('data-note', field.value);
        btn.classList.toggle('has-note', !!field.value);
        closeNoteEditor();
        FanHub.toast('success', 'Note updated.');
      });
    });

    doc.addEventListener(
      'click',
      function onOutside(event) {
        if (!editor.contains(event.target) && event.target !== btn) {
          closeNoteEditor();
          doc.removeEventListener('click', onOutside);
        }
      },
      true
    );
  }

  /* ---------------------------------------------------------------------------
     8. Ratings — contract §9.4
  ------------------------------------------------------------------------- */
  function paintStars(container, value) {
    $$('.fh-rate-star', container).forEach(function (star) {
      var starValue = Number(star.getAttribute('data-value'));
      star.classList.toggle('is-on', starValue <= value);
      star.setAttribute('aria-label', starValue + ' out of 10');
    });
  }

  function initRating(container) {
    if (container._fhRating) return;
    container._fhRating = true;

    var type = container.getAttribute('data-type');
    var id = container.getAttribute('data-id');
    var current = Number(container.getAttribute('data-value') || 0);
    paintStars(container, current);

    $$('.fh-rate-star', container).forEach(function (star) {
      star.addEventListener('mouseenter', function () {
        paintStars(container, Number(star.getAttribute('data-value')));
      });
      star.addEventListener('click', function () {
        var value = Number(star.getAttribute('data-value'));
        if (!value || value < 1 || value > 10) return;
        current = value;
        paintStars(container, current);
        FanHub.post(FanHub.urls.rate, { type: type, id: id, value: value }).then(function (res) {
          if (!res.ok) {
            FanHub.toast('error', 'Your rating could not be saved.');
            return;
          }
          container.setAttribute('data-value', String(value));
          FanHub.toast('success', 'Thanks! You rated this ' + value + '/10.');
          doc.dispatchEvent(
            new CustomEvent('fanhub:rate', { detail: { type: type, id: id, value: value } })
          );
        });
      });
    });

    container.addEventListener('mouseleave', function () {
      paintStars(container, current);
    });
  }

  /* ---------------------------------------------------------------------------
     9. Hero carousel — contract §9.5
  ------------------------------------------------------------------------- */
  function initHero(root) {
    if (root._fhHero) return;
    root._fhHero = true;

    var slides = $$('.fh-heroslide, .fh-hero-slide', root);
    if (slides.length < 2) return;

    var dots = $$('.fh-hero-dot', root);
    var index = Math.max(
      0,
      slides.findIndex(function (slide) {
        return slide.classList.contains('is-active');
      })
    );
    var timer = null;
    var paused = false;
    var interval = Number(root.getAttribute('data-interval')) || 6000;

    function show(next) {
      index = (next + slides.length) % slides.length;
      slides.forEach(function (slide, i) {
        var active = i === index;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
      });
      dots.forEach(function (dot, i) {
        dot.setAttribute('aria-current', i === index ? 'true' : 'false');
      });
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      stop();
      if (prefersReducedMotion() || paused || doc.hidden) return;
      timer = window.setInterval(function () {
        show(index + 1);
      }, interval);
    }

    root.addEventListener('mouseenter', function () {
      paused = true;
      stop();
    });
    root.addEventListener('mouseleave', function () {
      paused = false;
      start();
    });
    root.addEventListener('focusin', function () {
      paused = true;
      stop();
    });
    root.addEventListener('focusout', function (event) {
      if (!root.contains(event.relatedTarget)) {
        paused = false;
        start();
      }
    });
    doc.addEventListener('visibilitychange', function () {
      if (doc.hidden) stop();
      else start();
    });

    var prev = $('[data-hero-prev]', root);
    var next = $('[data-hero-next]', root);
    if (prev)
      prev.addEventListener('click', function () {
        show(index - 1);
        start();
      });
    if (next)
      next.addEventListener('click', function () {
        show(index + 1);
        start();
      });

    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () {
        show(i);
        start();
      });
    });

    // Swipe
    var startX = null;
    root.addEventListener(
      'touchstart',
      function (event) {
        startX = event.touches[0].clientX;
      },
      { passive: true }
    );
    root.addEventListener(
      'touchend',
      function (event) {
        if (startX === null) return;
        var delta = event.changedTouches[0].clientX - startX;
        if (Math.abs(delta) > 45) {
          show(delta < 0 ? index + 1 : index - 1);
          start();
        }
        startX = null;
      },
      { passive: true }
    );

    show(index);
    start();
  }

  /* ---------------------------------------------------------------------------
     10. Chat widget — contract §9.6
  ------------------------------------------------------------------------- */
  function initChat() {
    var modalEl = $('#chatModal');
    if (!modalEl) return;

    var form = $('#chatForm');
    var input = $('#chatInput');
    var messages = $('#chatMessages');
    var typing = $('#chatTyping');
    var retry = $('#chatRetry');
    var endpoint = (form && form.getAttribute('action')) || FanHub.urls.chat || '/chat';
    var sessionKey = 'fanhub_chat_session';
    var busy = false;

    function sessionId() {
      try {
        return window.sessionStorage.getItem(sessionKey) || '';
      } catch (e) {
        return '';
      }
    }

    function setSessionId(value) {
      try {
        if (value) window.sessionStorage.setItem(sessionKey, value);
      } catch (e) {
        /* noop */
      }
    }

    function scrollDown() {
      if (messages) messages.scrollTop = messages.scrollHeight;
    }

    /** markdown-lite: escape first, then apply a tiny subset of formatting. */
    function renderRich(text) {
      var safe = FanHub.escapeHtml(text);
      var out = safe
        .replace(/\r?\n/g, '<br>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em>$2</em>')
        .replace(/`([^`\n]+)`/g, '<code>$1</code>')
        .replace(/^\s*[-*]\s+(.*)$/gm, '<li>$1</li>')
        .replace(/(<li>[\s\S]*?<\/li>)(?=\s*<li>|<br>|$)/g, '<ul>$1</ul>')
        .replace(/<\/ul>\s*<ul>/g, '');
      return out;
    }

    function appendMessage(role, content, time) {
      if (!messages) return null;
      var wrap = doc.createElement('div');
      wrap.className = 'fh-chat-msg ' + (role === 'user' ? 'fh-chat-msg-user' : 'fh-chat-msg-bot');
      wrap.setAttribute(
        'aria-label',
        role === 'user' ? 'You said' : 'Fan Hub Assistant said'
      );

      var avatar = doc.createElement('span');
      avatar.className = 'fh-chat-mini-avatar';
      avatar.innerHTML = role === 'user' ? '<i class="bi bi-person-fill"></i>' : '<i class="bi bi-robot"></i>';
      avatar.setAttribute('aria-hidden', 'true');

      var bubble = doc.createElement('div');
      bubble.className = 'fh-chat-bubble';
      bubble.innerHTML = renderRich(content);

      wrap.appendChild(avatar);
      wrap.appendChild(bubble);

      if (time) {
        var stamp = doc.createElement('span');
        stamp.className = 'fh-chat-time';
        stamp.textContent = time;
        wrap.appendChild(stamp);
      }

      messages.insertBefore(wrap, typing || null);
      scrollDown();
      return wrap;
    }

    function setTyping(on) {
      if (!typing) return;
      typing.classList.toggle('is-visible', !!on);
      if (on) scrollDown();
    }

    function setRetry(on) {
      if (!retry) return;
      retry.style.display = on ? 'block' : 'none';
    }

    function send(text) {
      if (busy) return;
      text = (text || '').trim();
      if (!text) return;

      busy = true;
      setRetry(false);
      appendMessage('user', text, new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
      if (input) input.value = '';
      setTyping(true);

      FanHub.post(endpoint, { message: text, session_id: sessionId() })
        .then(function (res) {
          setTyping(false);
          busy = false;

          if (!res.ok || !res.data) {
            setRetry(true);
            if (input) input.value = text;
            return;
          }

          var payload = res.data.data || res.data;
          if (payload.session_id) setSessionId(payload.session_id);

          if (payload.conversation && payload.conversation.length) {
            var last = payload.conversation[payload.conversation.length - 1];
            if (last && last.role === 'assistant') return;
          }

          if (payload.reply) {
            appendMessage(
              'assistant',
              payload.reply,
              new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            );
          } else {
            setRetry(true);
            if (input) input.value = text;
          }
        })
        .catch(function () {
          setTyping(false);
          busy = false;
          setRetry(true);
          if (input) input.value = text;
        });
    }

    // Open / close
    $$('[data-chat-open]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        try {
          var instance = bootstrap.Modal.getOrCreateInstance(modalEl);
          instance.show();
        } catch (e) {
          modalEl.classList.add('show');
          modalEl.style.display = 'block';
        }
        window.setTimeout(function () {
          if (input) input.focus();
          scrollDown();
        }, 220);
      });
    });

    if (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        send(input ? input.value : '');
      });
    }

    if (input) {
      input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
          event.preventDefault();
          send(input.value);
        }
      });
    }

    if (retry) {
      var retryBtn = $('[data-chat-retry]', retry);
      if (retryBtn)
        retryBtn.addEventListener('click', function () {
          var text = input ? input.value : '';
          setRetry(false);
          send(text || 'Hello!');
        });
    }

    $$('[data-chat-prompt]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-chat-prompt') || '';
        if (input) input.value = text;
        send(text);
      });
    });

    scrollDown();
  }

  /* ---------------------------------------------------------------------------
     11. Confirm dialogs (data-confirm)
  ------------------------------------------------------------------------- */
  function initConfirms() {
    doc.addEventListener('submit', function (event) {
      var form = event.target;
      if (!(form instanceof HTMLFormElement)) return;
      var message = form.getAttribute('data-confirm');
      if (message && !window.confirm(message)) {
        event.preventDefault();
        event.stopPropagation();
      }
    });

    doc.addEventListener('click', function (event) {
      var el = event.target.closest ? event.target.closest('[data-confirm]') : null;
      if (!el || el instanceof HTMLFormElement) return;
      var message = el.getAttribute('data-confirm');
      if (message && !window.confirm(message)) {
        event.preventDefault();
      }
    });
  }

  /* ---------------------------------------------------------------------------
     12. Settings toggles (font-scale, reduced motion, theme)
  ------------------------------------------------------------------------- */
  var SETTING_MAP = {
    'font-scale': function (value, root) {
      root.setAttribute('data-font-scale', String(value));
      return String(value);
    },
    theme: function (value, root) {
      root.setAttribute('data-theme', value === 'light' ? 'light' : 'dark');
      return value === 'light' ? 'light' : 'dark';
    },
    'reduced-motion': function (value, root) {
      var reduced = value === 'reduced' || value === '1' || value === true || value === 'true';
      root.setAttribute('data-motion', reduced ? 'reduced' : 'full');
      return reduced ? 'reduced' : 'full';
    }
  };

  FanHub.applySetting = function (name, value) {
    var apply = SETTING_MAP[name];
    if (!apply) return false;
    apply(value, html);
    try {
      window.localStorage.setItem('fh:' + name, String(value));
    } catch (e) {
      /* noop */
    }
    return true;
  };

  function restoreLocalSettings() {
    // Signed-in users keep the server-rendered preferences (PUT /settings).
    // Guests get local persistence only.
    if (FanHub.user) return;

    Object.keys(SETTING_MAP).forEach(function (name) {
      try {
        var value = window.localStorage.getItem('fh:' + name);
        if (value !== null) SETTING_MAP[name](value, html);
      } catch (e) {
        /* noop */
      }
    });
  }

  function persistSettings(payload) {
    if (!FanHub.user) return;
    FanHub.post(FanHub.urls.settings, payload, 'PUT').then(function (res) {
      if (res.ok) FanHub.toast('success', 'Preference saved.');
    });
  }

  function initSettingControls() {
    $$('[data-setting]').forEach(function (control) {
      if (control._fhSetting) return;
      control._fhSetting = true;

      var name = control.getAttribute('data-setting');
      var current = html.getAttribute(
        name === 'font-scale' ? 'data-font-scale' : name === 'theme' ? 'data-theme' : 'data-motion'
      );
      if (control.tagName === 'SELECT' || control.type === 'checkbox') {
        if (control.type === 'checkbox') {
          control.checked =
            name === 'reduced-motion' ? current === 'reduced' : current === 'light' || control.checked;
        } else if (current) {
          control.value = current;
        }
      }

      control.addEventListener('change', function () {
        var value = control.type === 'checkbox' ? control.checked : control.value;
        if (name === 'reduced-motion') value = control.checked ? 'reduced' : 'full';
        if (name === 'theme') value = control.checked || control.value === 'light' ? 'light' : 'dark';
        FanHub.applySetting(name, value);

        if (control.getAttribute('data-persist') !== '0') {
          var payload = {};
          if (name === 'font-scale') payload.font_scale = Number(control.value);
          if (name === 'theme') payload.theme = control.value;
          if (name === 'reduced-motion') payload.reduced_motion = control.checked;
          persistSettings(payload);
        }
      });
    });

    // Simple [data-theme-toggle] buttons
    $$('[data-theme-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        FanHub.applySetting('theme', next);
        $$('input[data-setting="theme"]').forEach(function (input) {
          input.checked = next === 'light';
          if (input.tagName === 'SELECT') input.value = next;
        });
        persistSettings({ theme: next });
      });
    });
  }

  /* ---------------------------------------------------------------------------
     13. Sidebar toggle (tablet / small desktop overlay)
  ------------------------------------------------------------------------- */
  function initSidebarToggle() {
    var sidebar = $('.fh-sidebar');
    if (!sidebar) return;

    var backdrop = doc.createElement('div');
    backdrop.className = 'fh-sidebar-backdrop';
    backdrop.hidden = true;
    doc.body.appendChild(backdrop);

    function close() {
      sidebar.classList.remove('is-open');
      backdrop.hidden = true;
    }

    function toggle() {
      var open = sidebar.classList.toggle('is-open');
      backdrop.hidden = !open;
    }

    $$('[data-sidebar-toggle]').forEach(function (btn) {
      btn.addEventListener('click', function (event) {
        event.preventDefault();
        toggle();
      });
    });

    backdrop.addEventListener('click', close);
    doc.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') close();
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1200) close();
    });
  }

  /* ---------------------------------------------------------------------------
     14. Admin table row selection
  ------------------------------------------------------------------------- */
  function initTableSelection() {
    $$('.fh-table[data-selectable]').forEach(function (table) {
      var boxes = $$('input[data-row-select]', table);
      boxes.forEach(function (box) {
        box.addEventListener('change', function () {
          var row = box.closest('tr');
          if (row) row.classList.toggle('is-selected', box.checked);
          updateSelectedCount(table);
        });
      });

      var master = $('input[data-select-all]', table);
      if (master) {
        master.addEventListener('change', function () {
          boxes.forEach(function (box) {
            box.checked = master.checked;
            var row = box.closest('tr');
            if (row) row.classList.toggle('is-selected', box.checked);
          });
          updateSelectedCount(table);
        });
      }
    });
  }

  function updateSelectedCount(table) {
    var scope = table.closest('[data-select-scope]') || doc;
    var count = $$('input[data-row-select]:checked', table).length;
    $$('[data-selected-count]', scope).forEach(function (el) {
      el.textContent = String(count);
    });
    $$('[data-bulk-action]', scope).forEach(function (el) {
      el.disabled = count === 0;
    });
  }

  /* ---------------------------------------------------------------------------
     15. Chart.js bootstrapping from window.__CHARTS__
  ------------------------------------------------------------------------- */
  var CHART_PALETTE = ['#8B5CF6', '#EC4899', '#22D3EE', '#3B82F6', '#34D399', '#FBBF24', '#FB7185'];

  FanHub.charts = [];
  FanHub.chartPalette = CHART_PALETTE;

  FanHub.renderCharts = function () {
    try {
      if (!window.Chart || !Array.isArray(window.__CHARTS__)) return;

      Chart.defaults.color = '#94a3c4';
      Chart.defaults.borderColor = 'rgba(255,255,255,0.07)';
      Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
      Chart.defaults.font.size = 11;
      Chart.defaults.plugins.legend.labels.usePointStyle = true;
      Chart.defaults.plugins.legend.labels.boxWidth = 8;
      Chart.defaults.plugins.tooltip.backgroundColor = '#101a30';
      Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.12)';
      Chart.defaults.plugins.tooltip.borderWidth = 1;
      Chart.defaults.plugins.tooltip.padding = 10;
      Chart.defaults.plugins.tooltip.cornerRadius = 8;

      window.__CHARTS__.forEach(function (spec) {
        try {
          var canvas = doc.getElementById(spec.id);
          if (!canvas || !canvas.getContext) return;
          var config = spec.config || spec;
          config.options = config.options || {};
          config.options.responsive = config.options.responsive !== false;
          config.options.maintainAspectRatio = config.options.maintainAspectRatio || false;
          config.options.plugins = config.options.plugins || {};
          config.options.layout = config.options.layout || {};
          config.options.layout.padding = Object.assign({ top: 6, right: 8, bottom: 2, left: 4 }, config.options.layout.padding || {});

          var legend = config.options.plugins.legend || {};
          if (legend.display !== false) {
            legend.position = 'bottom';
            legend.labels = Object.assign(
              { padding: 16, boxWidth: 8, usePointStyle: true, pointStyle: 'circle' },
              legend.labels || {}
            );
            config.options.plugins.legend = legend;
          }

          if (config.options.scales) {
            if (config.options.scales.x && config.options.scales.x.ticks) {
              config.options.scales.x.ticks.maxRotation = 0;
              config.options.scales.x.ticks.autoSkip = true;
              config.options.scales.x.ticks.maxTicksLimit = config.indexAxis === 'y' ? 6 : 8;
            }
            if (config.indexAxis === 'y' && config.options.scales.y) {
              config.options.scales.y.ticks = config.options.scales.y.ticks || {};
              config.options.scales.y.ticks.autoSkip = false;
              config.options.scales.y.ticks.callback = function (value) {
                var label = this.getLabelForValue(value);
                label = label == null ? '' : String(label);
                return label.length > 18 ? label.slice(0, 16) + '…' : label;
              };
            }
          }

          if (config.data && config.data.datasets) {
            config.data.datasets.forEach(function (dataset, i) {
              if (!dataset.backgroundColor) {
                dataset.backgroundColor = CHART_PALETTE[i % CHART_PALETTE.length];
              }
              if (!dataset.borderColor && config.type === 'line') {
                dataset.borderColor = CHART_PALETTE[i % CHART_PALETTE.length];
              }
              if (config.type === 'line') {
                dataset.fill = dataset.fill !== undefined ? dataset.fill : true;
                dataset.tension = dataset.tension !== undefined ? dataset.tension : 0.4;
              }
            });
          }
          FanHub.charts.push(new Chart(canvas.getContext('2d'), config));
        } catch (err) {
          /* individual chart failure must not break the page */
        }
      });
    } catch (err) {
      /* noop */
    }
  };

  /* ---------------------------------------------------------------------------
     16. Delegated listeners
  ------------------------------------------------------------------------- */
  function initDelegates() {
    doc.addEventListener('click', function (event) {
      var target = event.target;
      if (!target || !target.closest) return;

      var bookmark = target.closest('[data-bookmark]');
      if (bookmark) {
        event.preventDefault();
        toggleBookmark(bookmark);
        return;
      }

      var note = target.closest('[data-bookmark-note]');
      if (note) {
        event.preventDefault();
        if (note.getAttribute('data-guest') === '1') {
          window.location.href = FanHub.urls.login;
          return;
        }
        openNoteEditor(note);
      }
    });
  }

  /* ---------------------------------------------------------------------------
     17. Entrance motion — skipped when the visitor prefers less motion
  ------------------------------------------------------------------------- */
  function motionOff() {
    return (
      doc.documentElement.getAttribute('data-motion') === 'reduced' ||
      (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)
    );
  }

  function initMotion() {
    if (motionOff() || !window.IntersectionObserver) return;

    var nodes = $$('.fh-poster, .fh-face, .fh-story, .fh-cat-tile, .fh-stat, .fh-admin-charts .fh-card, .fh-rail-card, .fh-help-card, .fh-fandom-card, .fh-cal-card, .fh-drop, .fh-join-card, .fh-quick a');
    if (!nodes.length) return;

    nodes.forEach(function (node, index) {
      node.classList.add('fh-rise');
      node.style.setProperty('--fh-d', String((index % 6) * 70) + 'ms');
    });

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-in');
          observer.unobserve(entry.target);
        });
      },
      { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }
    );

    nodes.forEach(function (node) {
      observer.observe(node);
    });
  }

  /* ---------------------------------------------------------------------------
     18. Boot everything
  ------------------------------------------------------------------------- */
  function boot() {
    try {
      restoreLocalSettings();
      runFlashBridge();
      watchImages(doc);
      initDelegates();
      initConfirms();
      initSettingControls();
      initSidebarToggle();
      initTableSelection();
      initChat();

      $$('[data-hero]').forEach(initHero);
      $$('.fh-rate-input[data-rate]').forEach(initRating);

      FanHub.refreshIcons();
      FanHub.renderCharts();
      initMotion();

      // Catch dynamically injected content (livewire-ish / AJAX lists).
      if (window.MutationObserver) {
        new MutationObserver(function (mutations) {
          mutations.forEach(function (mutation) {
            Array.prototype.forEach.call(mutation.addedNodes, function (node) {
              if (node.nodeType !== 1) return;
              watchImages(node);
              if (node.matches && node.matches('.fh-rate-input[data-rate]')) initRating(node);
              if (node.matches && node.matches('[data-hero]')) initHero(node);
            });
          });
        }).observe(doc.body, { childList: true, subtree: true });
      }
    } catch (err) {
      /* never surface a boot error to the user */
    }
  }

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  // Expose a couple of internals other views may need.
  FanHub.appendChatMessage = function (role, content) {
    var messages = $('#chatMessages');
    if (!messages) return;
    var wrap = doc.createElement('div');
    wrap.className = 'fh-chat-msg ' + (role === 'user' ? 'fh-chat-msg-user' : 'fh-chat-msg-bot');
    wrap.innerHTML =
      '<span class="fh-chat-mini-avatar"><i class="bi ' +
      (role === 'user' ? 'bi-person-fill' : 'bi-robot') +
      '"></i></span><div class="fh-chat-bubble">' +
      FanHub.escapeHtml(content) +
      '</div>';
    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
  };
})();
