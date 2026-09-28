{{--
    Floating assistant launcher + chat modal.
    Contract (assets/js/app.js):
      #chatForm[action]  → POST endpoint (JSON {message, session_id})
      #chatInput         → Enter sends, Shift+Enter newlines (multiline only)
      #chatMessages      → scroll area, aria-live="polite"
      #chatTyping        → .is-visible while waiting for the reply
      #chatRetry         → shown on failure, [data-chat-retry] resends
--}}
<button
    type="button"
    class="fh-chat-fab"
    data-chat-open
    aria-label="Open Fan Hub assistant"
    aria-controls="chatModal"
    title="Fan Hub assistant"
>
    <span class="fh-chat-fab-pulse" aria-hidden="true"></span>
    <i class="bi bi-robot" aria-hidden="true"></i>
</button>

<div
    class="modal fade fh-chat-modal"
    id="chatModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="chatModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="fh-chat-head">
                <span class="fh-chat-avatar" aria-hidden="true">
                    <i class="bi bi-robot"></i>
                    <span class="fh-chat-status"></span>
                </span>
                <div class="min-w-0">
                    <p class="fh-chat-title" id="chatModalLabel">Fan Hub Assistant</p>
                    <span class="fh-chat-eyebrow">Online · replies instantly</span>
                </div>
                <button
                    type="button"
                    class="btn-close ms-auto"
                    data-bs-dismiss="modal"
                    aria-label="Close chat"
                ></button>
            </div>

            <div class="fh-chat-body" id="chatMessages" role="log" aria-live="polite" aria-relevant="additions">
                <div class="fh-chat-msg fh-chat-msg-bot">
                    <span class="fh-chat-mini-avatar" aria-hidden="true"><i class="bi bi-robot"></i></span>
                    <div class="fh-chat-bubble">
                        <p>
                            Hey {{ auth()->check() ? \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') : 'there' }}!
                            I'm the <strong>Fan Hub Assistant</strong>. Ask me about upcoming
                            releases, conventions, characters or what's trending this week.
                        </p>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <button type="button" class="btn btn-fh-outline btn-sm" data-chat-prompt="What releases are coming up?">
                                Upcoming releases
                            </button>
                            <button type="button" class="btn btn-fh-outline btn-sm" data-chat-prompt="Which events are happening soon?">
                                Events near me
                            </button>
                        </div>
                    </div>
                </div>

                <div class="fh-chat-typing" id="chatTyping" aria-label="Assistant is typing">
                    <span></span><span></span><span></span>
                </div>
            </div>

            <div id="chatRetry" class="px-3 pt-2" style="display:none;">
                <div class="alert alert-danger mb-2 py-2 px-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1" aria-hidden="true"></i>
                    Message not sent.
                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-chat-retry>
                        Retry
                    </button>
                </div>
            </div>

            <form id="chatForm" action="{{ route('chat.store') }}" method="post">
                @csrf
                <div class="fh-chat-foot">
                    <label class="visually-hidden" for="chatInput">Message the Fan Hub assistant</label>
                    <input
                        id="chatInput"
                        class="fh-field"
                        type="text"
                        name="message"
                        placeholder="Ask about releases, events, characters…"
                        autocomplete="off"
                        maxlength="1000"
                    >
                    <button class="fh-chat-send" type="submit" aria-label="Send message">
                        <i class="bi bi-send-fill" aria-hidden="true"></i>
                    </button>
                </div>
                <p class="fh-chat-hint mb-0">
                    <i class="bi bi-lock" aria-hidden="true"></i>
                    Press Enter to send. Answers come from the Fan Hub knowledge base.
                </p>
            </form>
        </div>
    </div>
</div>
