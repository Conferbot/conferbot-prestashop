{* Conferbot widget loader — canonical snippet from the Conferbot dashboard,
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
    el.parentNode.insertBefore(js, el);
    js.onload = function () {
      var options = {$conferbot_options_json nofilter};
      window.ConferbotWidget(
        '{$conferbot_bot_id|escape:'javascript':'UTF-8'}',
        '{$conferbot_embed_type|escape:'javascript':'UTF-8'}',
        options
      );
    };
  })(document, 'script', 'conferbot-js');
</script>
