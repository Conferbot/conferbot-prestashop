<style>
  .cb-panel { max-width: 760px; margin: 20px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); padding: 32px 36px; font-size: 15px; }
  .cb-panel h2 { display: flex; align-items: center; gap: 12px; font-size: 24px; margin: 0 0 6px; }
  .cb-panel h2 img { width: 44px; height: 44px; }
  .cb-sub { color: #6b7280; margin-bottom: 24px; font-size: 15px; }
  .cb-status { border-radius: 10px; padding: 14px 18px; margin-bottom: 26px; font-size: 15px; display: flex; align-items: center; gap: 10px; }
  .cb-status.ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
  .cb-status.warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
  .cb-step { margin-bottom: 26px; }
  .cb-step-label { font-weight: 700; font-size: 17px; margin-bottom: 6px; display: block; }
  .cb-step-help { color: #6b7280; margin-bottom: 10px; }
  .cb-step-help a { font-weight: 600; }
  .cb-input { width: 100%; font-size: 18px; padding: 14px 16px; border: 2px solid #d1d5db; border-radius: 10px; letter-spacing: .5px; }
  .cb-input:focus { border-color: #2563eb; outline: none; }
  /* big friendly switch */
  .cb-switch { position: relative; display: inline-block; width: 64px; height: 34px; vertical-align: middle; }
  .cb-switch input { opacity: 0; width: 0; height: 0; }
  .cb-slider { position: absolute; cursor: pointer; inset: 0; background: #d1d5db; border-radius: 34px; transition: .2s; }
  .cb-slider:before { content: ""; position: absolute; height: 26px; width: 26px; left: 4px; bottom: 4px; background: #fff; border-radius: 50%; transition: .2s; }
  .cb-switch input:checked + .cb-slider { background: #16a34a; }
  .cb-switch input:checked + .cb-slider:before { transform: translateX(30px); }
  .cb-switch-text { font-size: 16px; margin-left: 12px; vertical-align: middle; }
  .cb-save { display: block; width: 100%; margin-top: 8px; background: #2563eb; color: #fff; border: none; border-radius: 10px; padding: 16px; font-size: 19px; font-weight: 700; cursor: pointer; }
  .cb-save:hover { background: #1d4ed8; }
  .cb-advanced { margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 18px; }
  .cb-advanced summary { cursor: pointer; color: #6b7280; font-weight: 600; font-size: 14px; }
  .cb-advanced .cb-adv-body { margin-top: 16px; }
  .cb-adv-row { margin-bottom: 18px; }
  .cb-adv-row label.cb-adv-label { font-weight: 600; display: block; margin-bottom: 6px; }
  .cb-pages label { display: inline-flex; align-items: center; gap: 6px; margin: 0 16px 8px 0; font-weight: 400; }
  .cb-pages input { width: 18px; height: 18px; }
  .cb-select, .cb-adv-input { font-size: 15px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; width: 100%; max-width: 420px; }
  .cb-verify { margin-top: 18px; text-align: center; color: #6b7280; }
  .cb-demo { margin-top: 8px; color: #6b7280; font-size: 14px; }
  .cb-demo a { font-weight: 600; }
</style>

<div class="cb-panel">
  <h2><img src="{$cb_logo|escape:'html':'UTF-8'}" alt="Conferbot"> {l s='Conferbot Live Chat' mod='conferbot'}</h2>
  <p class="cb-sub">{l s='Add a chat assistant to your store in under a minute. Only one thing to paste!' mod='conferbot'}</p>

  {if $cb_enabled && $cb_chatbot_id}
    <div class="cb-status ok">✅&nbsp;{l s='All set! The chat widget is live on your store.' mod='conferbot'}
      &nbsp;<a href="{$cb_shop_url|escape:'html':'UTF-8'}" target="_blank" rel="noopener">{l s='See it on my store →' mod='conferbot'}</a>
    </div>
  {elseif !$cb_chatbot_id}
    <div class="cb-status warn">👇&nbsp;{l s='Just one step: paste your Chatbot ID below and press the big blue button.' mod='conferbot'}</div>
  {else}
    <div class="cb-status warn">⏸️&nbsp;{l s='The chat is currently switched OFF. Turn it on below when you are ready.' mod='conferbot'}</div>
  {/if}

  <form action="{$cb_form_action|escape:'html':'UTF-8'}" method="post">
    <div class="cb-step">
      <label class="cb-step-label" for="cb-id">1. {l s='Paste your Chatbot ID' mod='conferbot'}</label>
      <div class="cb-step-help">
        {l s='Find it in your' mod='conferbot'} <a href="https://app.conferbot.com" target="_blank" rel="noopener">{l s='Conferbot dashboard' mod='conferbot'}</a>:
        {l s='open your bot, click "Share", then "Copy Bot ID". Pasting the whole code snippet also works - we will find the ID for you.' mod='conferbot'}
      </div>
      <input class="cb-input" type="text" id="cb-id" name="CONFERBOT_CHATBOT_ID"
             value="{$cb_chatbot_id|escape:'html':'UTF-8'}"
             placeholder="{l s='e.g. 64a1b2c3d4e5f6a7b8c9d0e1' mod='conferbot'}">
      <div class="cb-demo">
        {l s='No account yet?' mod='conferbot'}
        <a href="#" id="cb-use-demo" data-demo-id="{$cb_demo_bot_id|escape:'html':'UTF-8'}">{l s='Try our demo bot' mod='conferbot'}</a>
        {l s='- it works right away, no signup needed.' mod='conferbot'}
      </div>
    </div>

    <div class="cb-step">
      <span class="cb-step-label">2. {l s='Turn the chat on' mod='conferbot'}</span>
      <label class="cb-switch">
        <input type="checkbox" name="CONFERBOT_ENABLED" value="1" {if $cb_enabled}checked{/if}>
        <span class="cb-slider"></span>
      </label>
      <span class="cb-switch-text">{l s='Show the chat bubble on my store' mod='conferbot'}</span>
    </div>

    <button class="cb-save" type="submit" name="submitconferbot" value="1">
      {l s='Save - start chatting with my customers' mod='conferbot'} 🎉
    </button>

    <details class="cb-advanced">
      <summary>{l s='Advanced settings (optional - the defaults are fine)' mod='conferbot'}</summary>
      <div class="cb-adv-body">
        <div class="cb-adv-row">
          <label class="cb-adv-label" for="cb-embed">{l s='How the chat appears' mod='conferbot'}</label>
          <select class="cb-select" id="cb-embed" name="CONFERBOT_EMBED_TYPE">
            <option value="live_chat" {if $cb_embed_type == 'live_chat'}selected{/if}>{l s='Floating chat bubble (recommended)' mod='conferbot'}</option>
            <option value="popup_chat" {if $cb_embed_type == 'popup_chat'}selected{/if}>{l s='Popup window' mod='conferbot'}</option>
          </select>
        </div>
        <div class="cb-adv-row">
          <span class="cb-adv-label">{l s='Show the chat on these pages' mod='conferbot'}</span>
          <div class="cb-pages">
            {foreach from=$cb_pages item=page}
              <label>
                <input type="checkbox" name="CONFERBOT_SHOW_ON_PAGES_{$page.id|escape:'html':'UTF-8'}" value="1" {if $page.checked}checked{/if}>
                {$page.label|escape:'html':'UTF-8'}
              </label>
            {/foreach}
          </div>
        </div>
        <div class="cb-adv-row">
          <label class="cb-adv-label" for="cb-identity">{l s='Identity verification secret (optional - from Conferbot workspace settings > Security > Identity verification; lets Conferbot trust your logged-in customers\' names and emails)' mod='conferbot'}</label>
          <input class="cb-adv-input" type="password" id="cb-identity" name="CONFERBOT_IDENTITY_SECRET" autocomplete="new-password" value=""
            placeholder="{if $cb_identity_secret_saved}{l s='Saved - paste a new one to replace it' mod='conferbot'}{else}{l s='Paste your secret' mod='conferbot'}{/if}">
          {if $cb_identity_secret_saved}
            <label><input type="checkbox" name="CONFERBOT_IDENTITY_SECRET_CLEAR" value="1"> {l s='Remove the saved secret' mod='conferbot'}</label>
          {/if}
        </div>
        <div class="cb-adv-row">
          <label class="cb-adv-label" for="cb-script">{l s='Widget script URL (leave as is unless Conferbot support says otherwise)' mod='conferbot'}</label>
          <input class="cb-adv-input" type="text" id="cb-script" name="CONFERBOT_SCRIPT_URL" value="{$cb_script_url|escape:'html':'UTF-8'}">
        </div>
      </div>
    </details>
  </form>

  <p class="cb-verify">
    {l s='Need help? Write to' mod='conferbot'} <a href="mailto:support@conferbot.com">support@conferbot.com</a>
  </p>
</div>

<script type="text/javascript">
  document.getElementById('cb-use-demo').addEventListener('click', function (e) {
    e.preventDefault();
    var input = document.getElementById('cb-id');
    input.value = this.getAttribute('data-demo-id');
    var toggle = document.querySelector('.cb-switch input');
    if (toggle) { toggle.checked = true; }
    input.focus();
  });
</script>
