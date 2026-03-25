# HTML5 Modernisierung Plan - PS Chat Plugin

## Ziel
- **HTML5 Semantic**, moderne Struktur, bessere Accessibility
- **Rückwärtskompatibilität**: Alte Klassen-Namen bleiben für Custom CSS
- **Funktionalität** bleibt unverändert
- **Custom Stylebarkeit** erhalten

---

## 1. Chat-Box Container
### AKTUELL (Zeile 4385-4470)
```html
<div id="psource-chat-box-{id}" style="display:none;" class="psource-chat-box psource-chat-box-private...">
  {content}
</div>
```

### MODERN (mit Rückwärtskompatibilität)
```html
<aside id="psource-chat-box-{id}" role="complementary" aria-label="Chat {type}" 
       style="display:none;" 
       class="psource-chat-box psource-chat-box-private...">
  <!-- Kompatibilität: DIV-Klasse bleiben gleich -->
  {content}
</aside>
```

**Änderungen:**
- `<div>` → `<aside>` (semantisch korrekt für Chat-Widget)
- `role="complementary"` für Accessibility
- `aria-label` für Screen-Reader
- Alle Klassen BLEIBEN unverändert

---

## 2. Message Input Area
### AKTUELL (Zeile 4192-4309)
```html
<!-- Textarea -->
<textarea id="psource-chat-send-{id}" class="psource-chat-send"></textarea>

<!-- Meta-Buttons (ul.li soup) -->
<ul class="psource-chat-send-meta">
  <li class="psource-chat-send-input-length">
    <span class="psource-chat-character-count">0</span>/450
  </li>
  <li class="psource-chat-action-menu-item-sound-on">
    <a href="#" class="psource-chat-action-sound">Sound Icon</a>
  </li>
  <!-- More buttons... -->
</ul>
```

### MODERN
```html
<section class="psource-chat-module-message-area" role="region" aria-label="Message input">
  <!-- New: Semantic form wrapper -->
  <form class="psource-chat-input-form" role="search" aria-label="Send message">
    
    <!-- Textarea in fieldset for better semantics -->
    <fieldset class="psource-chat-input-fieldset">
      <textarea 
        id="psource-chat-send-{id}" 
        class="psource-chat-send" 
        aria-describedby="psource-chat-char-count-{id}"
        maxlength="{length}">
      </textarea>
      
      <!-- Character counter with aria-live for dynamic updates -->
      <div id="psource-chat-char-count-{id}" 
           class="psource-chat-character-count" 
           aria-live="polite" 
           aria-atomic="true">
        0/{max_length}
      </div>
    </fieldset>

    <!-- NEW: Semantic <nav> für Meta-Actions (statt ul.li) -->
    <nav class="psource-chat-send-meta" aria-label="Message options">
      <div class="psource-chat-send-meta-list" role="toolbar">
        
        <!-- Sound Toggle -->
        <button type="button" 
                class="psource-chat-action-sound psource-chat-action-menu-item-sound-on"
                aria-pressed="true"
                aria-label="Sound on/off"
                title="Toggle chat sound">
          <svg>...</svg>
        </button>

        <!-- File Upload -->
        <button type="button" 
                class="psource-chat-upload-button psource-chat-action-menu-item-file-upload"
                aria-label="Upload file">
          <svg>...</svg>
        </button>
        <input type="file" id="psource-chat-file-input-{id}" class="psource-chat-file-input" hidden multiple>

        <!-- Emoji Picker -->
        <button type="button" 
                class="psource-chat-emoticions-menu psource-chat-send-input-emoticons"
                aria-label="Open emoji picker"
                aria-expanded="false"
                aria-controls="psource-chat-emoji-picker-{id}">
          😊
        </button>
      </div>
    </nav>

    <!-- Emoji Picker Overlay -->
    <div id="psource-chat-emoji-picker-{id}" 
         class="psource-chat-emoji-picker psource-chat-emoticons-list"
         role="dialog"
         aria-modal="true"
         aria-hidden="true"
         aria-labelledby="psource-chat-emoji-button-{id}">
      <!-- Emoji items -->
    </div>

    <!-- Send Button -->
    <button type="submit" 
            id="psource-chat-send-button-{id}" 
            class="psource-chat-send-button"
            aria-label="Send message">
      Send
    </button>
  </form>
</section>
```

**Verbesserungen:**
- `<form>` statt loose `<div>` + buttons
- `<fieldset>` um Textarea (semantisch korrekt)
- `<nav role="toolbar">` statt `<ul>` für Meta-Buttons
- `aria-live="polite"` für Character-Count Updates
- `aria-pressed`, `aria-expanded` für Button-States
- `aria-modal`, `aria-hidden` für Emoji-Picker Dialog

**Rückwärtskompatibilität:**
- Alle alten Klassen-Namen BLEIBEN
- CSS kann immer noch `.psource-chat-send-meta li` selektieren (aber jetzt ist es ein `<div>`)
- jQuery-Selektoren funktionieren immer noch (DOM-Struktur ändert sich)

---

## 3. Message Row
### AKTUELL (Zeile 4865-5013)
```html
<div id="psource-chat-row-{timestamp}-{id}" class="psource-chat-row psource-chat-row-user">
  <p class="psource-chat-message">
    <a class="psource-chat-user psource-chat-user-avatar" href="#">
      <img src="avatar.jpg" />
    </a>
    Message text...
    <br/>
    <span class="date">2025-03-25</span>
    <span class="time">14:32</span>
  </p>
  
  <ul class="psource-chat-row-footer">
    <li class="psource-chat-admin-actions-item psource-chat-user-invite">
      <a class="psource-chat-user-invite" href="#">...</a>
    </li>
    <!-- More actions -->
  </ul>
</div>
```

### MODERN
```html
<article id="psource-chat-row-{timestamp}-{id}" 
         class="psource-chat-row psource-chat-row-user"
         role="article"
         aria-label="Message from {username}">
  
  <!-- Message Header mit Metadata -->
  <header class="psource-chat-row-header">
    <figure class="psource-chat-row-figure">
      <a class="psource-chat-user psource-chat-user-avatar" 
         href="#" 
         aria-label="View profile: {username}">
        <img src="avatar.jpg" 
             alt="{username}"
             class="psource-chat-user psource-chat-user-avatar" />
      </a>
      <figcaption>
        <a class="psource-chat-user psource-chat-user-name" 
           href="#"
           aria-label="Username: {username}">
          {username}
        </a>
      </figcaption>
    </figure>
    
    <!-- Timestamp -->
    <time class="psource-chat-row-time" datetime="{timestamp}">
      <span class="date new">{formatted_date}</span>
      <span class="time">{formatted_time}</span>
    </time>
  </header>

  <!-- Message Content -->
  <section class="psource-chat-row-content">
    <p class="psource-chat-message">
      {message_with_media}
    </p>
  </section>

  <!-- Moderator Actions -->
  {% if moderator %}
  <footer class="psource-chat-row-footer" role="toolbar" aria-label="Message actions">
    <div class="psource-chat-row-actions">
      
      <!-- Invite to Private Chat -->
      <button type="button"
              class="psource-chat-user-invite psource-chat-admin-actions-item psource-chat-user-invite"
              data-user="{user_hash}"
              aria-label="Invite to private chat: {username}"
              title="Invite to private chat">
        <svg><!-- Lock icon --></svg>
      </button>

      <!-- Delete/Hide Message -->
      <button type="button"
              class="psource-chat-admin-actions-item-delete psource-chat-admin-actions-item"
              aria-label="Moderate message"
              title="Hide message">
        Hide
      </button>

      <!-- Block IP -->
      {% if ip_blocking_enabled %}
      <button type="button"
              class="psource-chat-admin-actions-item-block-ip psource-chat-admin-actions-item"
              data-ip="{ip_address}"
              aria-label="Block IP: {ip_address}"
              title="Block IP address">
        {ip_address}
      </button>
      {% endif %}
    </div>
  </footer>
  {% endif %}
</article>
```

**Verbesserungen:**
- `<article>` statt `<div>` (Semantic HTML5)
- `<header>` für Benutzer-Info + Timestamp
- `<figure>` + `<figcaption>` für Avatar + Name (semantisch korrekt für illustrative Inhalte)
- `<time datetime="...">` für Datum/Zeit (maschinenlesbar)
- `<section>` für Message-Content
- `<footer role="toolbar">` für Actions
- `<button>` statt `<a href="#">` für Actions (bessere Accessibility)
- `aria-label` auf allen wichtigen Elementen
- `data-*` Attribute statt `rel=""` (HTML5 konform)

**Rückwärtskompatibilität:**
- Alle Klassen-Namen BLEIBEN
- `.psource-chat-row` funktioniert weiterhin
- `.psource-chat-admin-actions-item li` wird zu `.psource-chat-admin-actions-item button` (aber CSS kann angepasst werden)
- jQuery-Selektoren: `.find('.psource-chat-user-invite')` funktioniert immer noch

---

## 4. Implementierungs-Roadmap

### Phase 1: Message Input Area (chat_session_message_area_module)
- Refactor zu `<form>` + `<nav>`
- Add ARIA labels
- Update CSS-Selektoren (mit Fallback)
- **Keine Breaking Changes**

### Phase 2: Message Row (chat_session_build_row)
- Refactor zu `<article>` + `<header>` + `<footer>`
- Update zeitformat mit `<time>`
- Convert `<a>` zu `<button>` für Actions
- **Keine Breaking Changes**

### Phase 3: Chat Box Container (chat_box_container)
- Refactor zu `<aside>`
- Add `role="complementary"`, `aria-label`
- **Keine Breaking Changes**

### Phase 4: CSS-Updates
- Update Selektoren auf neue HTML-Struktur
- Fallback-Selektoren für alte `<div>` (if needed)
- Modern Phase 1 CSS anpassen

---

## 5. Security Improvements

**Bestehend:**
- `esc_html()` auf Namen
- `esc_attr()` auf Attribute
- `wp_kses_post()` auf Message-Content

**Zusätzlich:**
- `data-*` Attribute für User/IP Data (besser als `rel=""`)
- `aria-` Attributes sind nicht HTML-gefährlich
- Form-Input Validierung (HTML5 `maxlength`, etc.)

---

## 6. Backward Compatibility Matrix

| Feature | Old | New | Breaking? |
|---------|-----|-----|-----------|
| CSS Selektoren | `.psource-chat-row` | same | ❌ No |
| jQuery `.find('.psource-chat-send')` | Works | Works | ❌ No |
| Custom Admin CSS | Works | Works | ❌ No |
| Emoji Picker | `<ul>` | `<nav>` | ✅ Maybe |
| Message Buttons | `<a href="#">` | `<button>` | ✅ Maybe |

**Mitigations:**
- Keep old classes on new tags
- Use CSS `display: flex` instead of `float` (modern CSS already does this)
- JS can query `data-*` attributes (jQuery still works)

---

## 7. Custom CSS Stylebarkeit

Developers can still customize:
```css
/* Old selectors still work */
.psource-chat-box { ... }
.psource-chat-row { ... }
.psource-chat-send-meta li { ... }

/* New semantic selectors available */
aside.psource-chat-box { ... }
article.psource-chat-row header { ... }
nav.psource-chat-send-meta button { ... }
```

---

## Next Steps

1. Start with **Phase 1** (Input Area modernization)
2. Test with existing CSS
3. Verify jQuery functionality
4. Proceed to Phase 2 (Message Rows)
5. And so on...

---

**Version**: 1.0 | **Status**: Planning | **Last Updated**: 2026-03-25
