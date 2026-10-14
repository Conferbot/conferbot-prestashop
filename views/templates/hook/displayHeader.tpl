{* Conferbot widget loader - canonical snippet from the Conferbot dashboard,
   with customer/store context passed as the third ConferbotWidget argument. *}
<script type="text/javascript">
  (function (d, s, id) {
    var js, el = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s);
    js.async = true;
    js.src = '{$conferbot_script_url|escape:'javascript':'UTF-8'}';
    js.id = id;
    js.charset = 'UTF-8';
    if (el && el.parentNode) { el.parentNode.insertBefore(js, el); } else { d.head.appendChild(js); }
    js.onload = function () {
      var options = {$conferbot_options_json nofilter};
      window.ConferbotWidget(
        '{$conferbot_bot_id|escape:'javascript':'UTF-8'}',
        '{$conferbot_embed_type|escape:'javascript':'UTF-8'}',
        options
      );
      // What the visitor did, for Conferbot's "chats that led to a sale":
      // the order on the confirmation page (once per order) and cart changes.
      var events = {$conferbot_events_json nofilter};
      events.forEach(function (e) {
        try {
          if (e.once) {
            if (sessionStorage.getItem('cb-track-' + e.once)) return;
            sessionStorage.setItem('cb-track-' + e.once, '1');
          }
        } catch (err) {}
        window.ConferbotWidget.track(e.name, e.props || {});
      });
      if (window.prestashop && typeof window.prestashop.on === 'function') {
        window.prestashop.on('updateCart', function () { window.ConferbotWidget.track('cart_updated'); });
      }
    };
  })(document, 'script', 'conferbot-js');
</script>
