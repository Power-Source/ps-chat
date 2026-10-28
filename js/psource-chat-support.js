(function () {
    'use strict';
    const config = window.psChatSupport;
    if (!config) return;
    const text = config.strings;
    const currentUserId = Number(config.userId);
    const nodes = [];
    let sequence = 0;
    const requestId = () => window.crypto && window.crypto.randomUUID
        ? window.crypto.randomUUID() : Date.now().toString(36) + '-' + Math.random().toString(36).slice(2);
    const element = (tag, className, content) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (content !== undefined) node.textContent = content;
        return node;
    };
    const button = (label, action) => {
        const node = element('button', '', label);
        node.type = 'button';
        node.addEventListener('click', action);
        return node;
    };
    async function api(path, body) {
        const headers = { Accept: 'application/json' };
        if (config.staff) headers['X-WP-Nonce'] = config.nonce;
        if (body !== undefined) headers['Content-Type'] = 'application/json';
        const response = await fetch(config.url + path, {
            method: body === undefined ? 'GET' : 'POST',
            credentials: 'same-origin', cache: 'no-store', headers,
            body: body === undefined ? undefined : JSON.stringify(body)
        });
        const data = await response.json();
        if (!response.ok) {
            const error = new Error(data.message || text.error);
            error.status = response.status;
            throw error;
        }
        return data;
    }

    class SupportChat {
        constructor(root) {
            this.root = root;
            this.staff = root.dataset.psSupport === 'staff';
            this.open = this.staff || !root.classList.contains('ps-chat-support-floating');
            this.session = null;
            this.lastId = 0;
            this.rendered = new Set();
            this.pending = null;
            this.timer = null;
            this.busy = false;
            this.polling = false;
            this.revision = 0;
            this.id = 'ps-support-' + (++sequence);
            if (!this.staff) {
                this.launcher = button(root.dataset.label || 'Support', () => this.toggle());
                this.launcher.className = 'ps-support-launcher';
                this.launcher.setAttribute('aria-controls', this.id);
                root.append(this.launcher);
            }
            this.panel = element('section', 'ps-support-panel');
            this.panel.id = this.id;
            this.panel.setAttribute('aria-label', root.dataset.label || 'Support');
            this.panel.hidden = !this.open;
            this.root.append(this.panel);
            const heading = element('header', 'ps-support-header');
            heading.append(element('strong', '', root.dataset.label || 'Support'));
            if (!this.staff) heading.append(button(text.hide, () => this.toggle()));
            this.panel.append(heading);
            this.notice = element('p', 'ps-support-notice');
            this.notice.setAttribute('role', 'status');
            this.error = element('p', 'ps-support-error');
            this.error.setAttribute('role', 'alert');
            this.warning = element('p', 'ps-support-error');
            this.warning.setAttribute('role', 'status');
            this.panel.append(this.notice, this.warning, this.error);
            this.panel.addEventListener('keydown', event => {
                if (event.key === 'Escape' && !this.staff && this.open) this.toggle();
            });
            if (this.staff) this.buildInbox();
            this.content = element('div', 'ps-support-content');
            this.panel.append(this.content);
            this.log = element('div', 'ps-support-log');
            this.log.setAttribute('role', 'log');
            this.log.setAttribute('aria-label', text.message);
            this.log.setAttribute('aria-live', 'polite');
            this.content.append(this.log);
            this.buildForm();
            this.actions = element('div', 'ps-support-actions');
            this.claimButton = button(text.claim, () => this.action('claim'));
            this.notifyButton = button(text.notify, () => this.action('notify'));
            this.closeButton = button(this.staff ? text.resolve : text.close, () => this.action(this.staff ? 'resolve' : 'close'));
            this.newButton = button(text.newChat, () => this.newSession());
            this.actions.append(this.claimButton, this.notifyButton, this.closeButton, this.newButton);
            this.panel.append(this.actions);
            this.claimButton.hidden = true;
            this.notifyButton.hidden = true;
            this.closeButton.hidden = true;
            this.newButton.hidden = true;
            if (this.launcher) this.launcher.setAttribute('aria-expanded', String(this.open));
            if (this.open) this.initialize();
        }

        buildInbox() {
            this.inbox = element('nav', 'ps-support-inbox');
            this.inbox.setAttribute('aria-label', 'Support');
            this.panel.append(this.inbox);
            const refresh = button(text.refresh, () => this.initialize());
            this.panel.append(refresh);
            this.selected = this.root.dataset.session || new URLSearchParams(location.search).get('session') || '';
        }

        buildForm() {
            this.form = element('form', 'ps-support-form');
            this.form.hidden = true;
            this.contact = element('div', 'ps-support-contact');
            this.category = element('select');
            this.category.id = this.id + '-category';
            const catLabel = element('label', '', text.category);
            catLabel.htmlFor = this.category.id;
            this.name = this.input(text.name, 'text', 'name');
            this.email = this.input(text.email, 'email', 'email');
            this.contact.append(catLabel, this.category, this.name.label, this.name.input, this.email.label, this.email.input);
            this.message = element('textarea');
            this.message.id = this.id + '-message';
            this.message.maxLength = 4000;
            this.message.required = true;
            this.message.rows = 3;
            const messageLabel = element('label', '', text.message);
            messageLabel.htmlFor = this.message.id;
            this.submit = element('button', '', text.start);
            this.submit.type = 'submit';
            this.form.append(this.contact, messageLabel, this.message, this.submit);
            this.intro = element('p', 'ps-support-intro');
            this.intro.hidden = true;
            this.bot = element('section', 'ps-support-bot');
            this.bot.hidden = true;
            this.bot.setAttribute('aria-label', text.botSummary);
            this.content.append(this.intro, this.bot, this.form);
            this.form.addEventListener('submit', event => {
                event.preventDefault();
                this.send();
            });
        }

        input(label, type, name) {
            const input = element('input');
            input.id = this.id + '-' + name;
            input.type = type;
            input.maxLength = type === 'email' ? 254 : 100;
            input.autocomplete = name;
            const node = element('label', '', label);
            node.htmlFor = input.id;
            return { input, label: node };
        }

        async initialize() {
            if (this.initializing || this.busy) {
                this.initializeAgain = true;
                return;
            }
            this.initializing = true;
            this.submit.disabled = true;
            try {
                this.error.textContent = '';
                if (this.staff) {
                    await this.refreshInbox();
                    if (this.selected) await this.load();
                    else this.notice.textContent = text.choose;
                } else {
                    const policy = await api('config');
                    const categoryId = this.category.value;
                    this.category.replaceChildren();
                    const general = element('option', '', text.general);
                    general.value = '0';
                    this.category.append(general);
                    policy.categories.forEach(category => {
                        const option = element('option', '', category.name);
                        option.value = category.id;
                        this.category.append(option);
                    });
                    if (Array.from(this.category.options).some(option => option.value === categoryId)) {
                        this.category.value = categoryId;
                    }
                    this.name.input.required = policy.require_name;
                    this.email.input.required = policy.require_email;
                    this.name.label.textContent = text.name + (policy.require_name ? ' *' : ' (' + text.optional + ')');
                    this.email.label.textContent = text.email + (policy.require_email ? ' *' : ' (' + text.optional + ')');
                    this.intro.textContent = policy.intro_text;
                    this.botPolicy = policy.bot;
                    try {
                        await this.load();
                    } catch (error) {
                        if (error.status !== 401) throw error;
                        this.newSession(false);
                    }
                }
                this.schedule();
            } catch (error) {
                this.showError(error);
                this.form.hidden = true;
                this.bot.hidden = true;
            } finally {
                this.initializing = false;
                this.submit.disabled = this.busy;
                if (this.initializeAgain) {
                    this.initializeAgain = false;
                    if (this.open) this.initialize();
                }
            }
        }

        toggle() {
            this.open = !this.open;
            this.panel.hidden = !this.open;
            this.launcher.setAttribute('aria-expanded', String(this.open));
            clearTimeout(this.timer);
            if (this.open) {
                this.initialize();
                this.panel.querySelector('button').focus();
            } else {
                this.launcher.focus();
            }
        }

        newSession(reset = true) {
            this.session = null;
            this.lastId = 0;
            this.rendered.clear();
            this.log.replaceChildren();
            if (reset) this.pending = null;
            this.contact.hidden = false;
            this.form.hidden = false;
            if (reset) this.form.reset();
            this.intro.hidden = !this.intro.textContent;
            this.closeButton.hidden = true;
            this.newButton.hidden = true;
            this.submit.textContent = text.start;
            this.notice.textContent = '';
            this.warning.textContent = '';
            this.error.textContent = '';
            if (reset) {
                this.botStep = null;
                this.botTopic = null;
            }
            this.renderBot();
        }

        renderBot() {
            const policy = this.botPolicy;
            this.bot.hidden = true;
            if (this.staff || this.session || !policy || !policy.enabled_chat || !policy.topics.length) return;
            this.bot.hidden = false;
            if (this.botTopic && this.botStep !== 'handoff') {
                this.botTopic = policy.topics.find(topic => topic.id === this.botTopic.id) || null;
            }
            this.bot.replaceChildren();
            this.bot.append(element('strong', '', policy.name));
            const content = element('div', 'ps-support-bot-body');
            this.bot.append(content);
            const actions = element('div', 'ps-support-bot-actions');
            const handoff = () => {
                if (this.botTopic) {
                    const summary = text.botSummary + ': ' + this.botTopic.title + '\n' +
                        this.botTopic.answer + '\n\n' + text.botDetails + '\n';
                    this.message.value = summary + this.message.value;
                    this.category.value = String(this.botTopic.category_id);
                }
                this.botStep = 'handoff';
                this.renderBot();
                if (this.message.value.length > this.message.maxLength) this.error.textContent = text.messageTooLong;
                this.message.focus();
            };
            if (this.botStep === 'handoff') {
                content.append(element('p', '', text.botHandoff));
                this.form.hidden = false;
                return;
            }
            this.form.hidden = true;
            if (this.botStep === 'solved') {
                content.append(element('p', '', text.botDone));
            } else if (this.botTopic) {
                content.append(element('h3', '', this.botTopic.title), element('p', 'ps-support-bot-answer', this.botTopic.answer),
                    element('p', '', text.botHelp));
                actions.append(button(text.botSolved, () => {
                    this.botStep = 'solved';
                    this.renderBot();
                    this.bot.querySelector('.ps-support-bot-body').focus();
                }), button(text.botHuman, handoff));
            } else {
                content.append(element('p', '', policy.greeting));
                policy.topics.forEach(topic => {
                    actions.append(button(topic.title, () => {
                        this.botTopic = topic;
                        this.renderBot();
                        this.bot.querySelector('.ps-support-bot-body').focus();
                    }));
                });
                actions.append(button(text.botHuman, handoff));
            }
            if (this.botTopic || this.botStep === 'solved') {
                actions.append(button(text.botBack, () => {
                    this.botTopic = null;
                    this.botStep = null;
                    this.renderBot();
                    this.bot.querySelector('.ps-support-bot-body').focus();
                }));
            }
            content.tabIndex = -1;
            this.bot.append(actions);
        }

        schedule() {
            clearTimeout(this.timer);
            if (!this.open) return;
            this.timer = setTimeout(async () => {
                if (this.polling || this.busy) return this.schedule();
                this.polling = true;
                try {
                    if (this.staff) await this.refreshInbox();
                    if ((this.staff && this.selected) || (!this.staff && this.session)) await this.load();
                } catch (error) {
                    this.showError(error);
                } finally {
                    this.polling = false;
                    this.schedule();
                }
            }, this.staff ? 5000 : 3000);
        }

        async refreshInbox() {
            const rows = await api('inbox');
            this.inbox.replaceChildren();
            if (!rows.length) this.inbox.append(element('p', '', text.empty));
            rows.forEach(row => {
                const item = button(row.name + ' · ' + row.category + ' · ' + row.state + ' · ' + text.site + ' ' + row.origin_blog_id +
                    (row.notification_status === 'failed' ? ' · ' + text.mailFailed : ''), async () => {
                    if (this.busy) return;
                    this.selected = row.id;
                    this.lastId = 0;
                    this.rendered.clear();
                    this.log.replaceChildren();
                    this.pending = null;
                    try { await this.load(); } catch (error) { this.showError(error); }
                });
                item.setAttribute('aria-current', row.id === this.selected ? 'true' : 'false');
                this.inbox.append(item);
            });
            if (!this.selected) this.form.hidden = true;
        }

        async load() {
            const selected = this.selected;
            const revision = this.revision;
            const path = this.staff ? 'conversation/' + selected : 'session';
            const data = await api(path + '?since=' + this.lastId);
            if (revision !== this.revision || (this.staff && selected !== this.selected)) return;
            this.update(data);
        }

        update(data) {
            if (this.session && this.session.id !== data.id) {
                this.lastId = 0;
                this.rendered.clear();
                this.log.replaceChildren();
            }
            this.session = data;
            this.bot.hidden = true;
            this.intro.hidden = true;
            this.contact.hidden = true;
            this.submit.textContent = text.send;
            const canReply = data.state === 'waiting' || data.state === 'active';
            this.form.hidden = !canReply || (this.staff && data.assigned_user_id !== currentUserId);
            this.closeButton.hidden = !canReply || (this.staff && data.assigned_user_id !== currentUserId);
            this.claimButton.hidden = !this.staff || data.state !== 'waiting';
            this.notifyButton.hidden = !this.staff || data.state !== 'waiting';
            this.newButton.hidden = this.staff || canReply;
            this.notice.textContent = data.state === 'waiting' ? text.waiting :
                data.state === 'active' ? text.active + ' ' + data.agent :
                    data.state === 'expired' ? text.expired : text.closed;
            if (this.staff) {
                this.notice.textContent += ' · ' + text.site + ' ' + data.origin_blog_id + ' · ' + data.name +
                    (data.email ? ' · ' + data.email : '');
                if (data.assigned_user_id && data.assigned_user_id !== currentUserId) this.notice.textContent += ' · ' + text.claimedOther;
            }
            this.warning.textContent = data.notification_status === 'failed' ? text.mailFailed : '';
            const nearBottom = this.log.scrollHeight - this.log.scrollTop - this.log.clientHeight < 60;
            data.messages.forEach(message => {
                if (this.rendered.has(message.id)) return;
                this.rendered.add(message.id);
                this.lastId = Math.max(this.lastId, message.id);
                const article = element('article', 'ps-support-message ps-support-message-' + message.kind);
                const time = element('time', '', new Date(message.created_at).toLocaleString());
                time.dateTime = message.created_at;
                article.append(element('strong', '', message.name), time, element('p', '', message.body));
                this.log.append(article);
            });
            if (nearBottom) this.log.scrollTop = this.log.scrollHeight;
        }

        async send() {
            if (this.message.value.length > this.message.maxLength) {
                this.error.textContent = text.messageTooLong;
                this.message.focus();
                return;
            }
            if (this.busy || this.initializing || !this.form.reportValidity()) return;
            this.busy = true;
            this.revision++;
            this.submit.disabled = true;
            const message = this.message.value;
            if (!this.pending || this.pending.message !== message) this.pending = { message, client_id: requestId() };
            const body = { ...this.pending, since: this.lastId };
            const starting = !this.session;
            if (starting) Object.assign(body, { name: this.name.input.value, email: this.email.input.value, category_id: Number(this.category.value) });
            try {
                const path = this.staff ? 'reply/' + this.selected : starting ? 'start' : 'message';
                const data = await api(path, body);
                this.update(data);
                this.error.textContent = '';
                if (this.message.value === message) this.message.value = '';
                this.pending = null;
                this.schedule();
            } catch (error) {
                this.showError(error);
            } finally {
                this.submit.disabled = false;
                this.busy = false;
                if (this.initializeAgain && this.open) {
                    this.initializeAgain = false;
                    this.initialize();
                }
            }
        }

        async action(action) {
            if (this.busy || this.initializing || !this.session) return;
            this.busy = true;
            this.revision++;
            try {
                const data = await api(this.staff ? action + '/' + this.selected : action, {});
                this.error.textContent = '';
                if (action === 'claim') this.update(data);
                else {
                    await this.load();
                    if (action === 'notify') this.error.textContent = text.notified;
                }
            } catch (error) {
                this.showError(error);
                if (error.status === 409) {
                    try { await this.load(); } catch (loadError) { this.showError(loadError); }
                }
            } finally {
                this.busy = false;
                if (this.initializeAgain && this.open) {
                    this.initializeAgain = false;
                    this.initialize();
                }
            }
        }

        showError(error) {
            this.error.textContent = error.message || text.error;
            if (!this.staff && error.status === 401) {
                this.form.hidden = true;
                this.newButton.hidden = false;
                this.closeButton.hidden = true;
            }
        }
    }
    function init() {
        document.querySelectorAll('[data-ps-support]').forEach(root => nodes.push(new SupportChat(root)));
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
    window.addEventListener('pagehide', () => nodes.forEach(chat => clearTimeout(chat.timer)));
    window.addEventListener('pageshow', event => {
        if (event.persisted) nodes.forEach(chat => chat.schedule());
    });
}());
