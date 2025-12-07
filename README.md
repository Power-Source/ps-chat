# PS Chat – Selbstgehosteter WordPress‑Chat

PS Chat ist ein leistungsfähiges, komplett selbstgehostetes Chat‑Plugin für WordPress. Es bietet Direktnachrichten, Gruppen‑Chats (BuddyPress), Medien‑Einbettungen und eine moderne Emoji‑Auswahl – ohne externe Dienste.

## Highlights

- Chat in Beiträgen/Seiten, Widgets oder als Dock unten rechts
- Private Chats zwischen angemeldeten Nutzern (Einladungen, Re‑Open)
- BuddyPress‑Gruppen‑Chats mit Moderation
- Medienunterstützung: Link‑Previews, Bilder, YouTube
- Moderner Emoji‑Picker (Suche, Kategorien, kompaktes Grid)
- Dateiuploads mit Vorschau (optional)
- Transienten‑Caching, optimierte Polling‑Routen
- Mehrsprachigkeit (Deutsch, EN, FR, IT)

## Funktionen im Detail

- Direktnachrichten: 1:1‑Chats, Einladungen, Status „available/busy/invisible“
- Gruppen: BuddyPress‑Gruppenräume inkl. Admin/Mod‑Rechten
- Medien: Automatische Erkennung und Rendering von URLs (Link‑Preview, Bild, Video)
- Emoji: Modal‑Picker im Chatfenster mit Suche und Tabs
- Uploads: Drag & Drop/Dateiwähler, saubere Einbettung in Nachrichten
- Moderation: Nachrichten ausblenden, IP‑Verwaltung, Benutzersperren
- Performance: Transienten, leichtgewichtige Polling‑Endpunkte

## Installation

1. ZIP (`ps-chat-<version>.zip`) über „Plugins → Installieren → Plugin hochladen“ einspielen.
2. Aktivieren und „PS Chat“ in den Einstellungen konfigurieren.
3. Optional: BuddyPress aktivieren für Gruppen‑Chats.

## Einbindung

- Shortcode: `[[psource_chat]]` in Seite/Beitrag einfügen
- Widget: „PS Chat Widget“ im Customizer/Widgets‑Bereich
- Dock unten rechts: in den Plugin‑Optionen aktivieren

## Wichtige Optionen

- Poll‑Intervalle (Nachrichten/Meta/Benutzer) für Serverlast feinjustieren
- Sendebutton‑Position: „unterhalb“ (Text) oder „rechts“ (Icon)
- Emoji & Sound: Aktivieren/Deaktivieren, Lautstärke
- Uploads: global aktivieren, Größenlimits
- Moderation: gesperrte Wörter/IPs, Rollenrechte

## Sicherheit

- Nonce‑Prüfungen für AJAX
- Eingaben werden mit `sanitize_*` und `wp_kses_post` bereinigt
- Netzwerkweite Tabellen (Multisite) über `$wpdb->base_prefix`

## Kompatibilität

- WordPress ≥ 4.9 (getestet bis 6.8.x)
- PHP ≥ 7.0 (Ziel: 8.0+)
- BuddyPress (optional)

## Performance‑Tipps

- Poll‑Intervalle moderat einstellen (Nachrichten ≥ 1s)
- Medien‑Caching aktivieren
- Minimierte Chats ggf. seltener pollen

## Entwicklerhinweise

- Hooks
  - `psource_chat_before_save_message` – Inhalt vor dem Speichern transformieren
  - `psource_chat_display_message` – Rendering im Frontend anpassen
- Klassen
  - `PSOURCE_Chat` (Core), `PSource_Chat_AJAX` (REST/AJAX),
    `PSource_Chat_Media`, `PSource_Chat_Upload`, `PSource_Chat_Avatar`, `PSource_Chat_Emoji`
- Internationalisierung: Strings mit `__()`/`_e()` (`psource-chat`)

## Changelog (Kurzfassung)

- 1.1.0: Emoji‑Picker als Modal, kompaktere Emojis; Sendebutton rechts als Icon; Fix für 500er im Poll; JS‑Cleanup
- 1.0.0: Erste Veröffentlichung

## Support & Quellen

- Projekt: https://power-source.github.io/ps-chat/
- GitHub: https://github.com/Power-Source/ps-chat
- Issues: https://github.com/Power-Source/ps-chat/issues
