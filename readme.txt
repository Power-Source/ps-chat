=== PS Chat ===
Contributors: DerNerd (PSOURCE)
Tags: multisite, chat, community, messenger, support
Requires at least: 4.9
Tested up to: 6.8.1
ClassicPress: 2.6.0
Stable tag: 1.1.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Erlaube Deinen Usern, mit Dir oder anderen Usern zu chatten. Inkl. Gruppenchats. Keine Drittanbieter, keine externen Abo-Kosten!

== Description ==

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

== ChangeLog ==

= 1.1.2 =

* Fix: PHP-Warnung behoben (`Undefined variable $filter_session_type`) in Session-Logs (`PSOURCEChat_Session_Logs_Table::prepare_items()`)
* Fix: Filter-Variablen in Session-Logs vor Verwendung konsistent initialisiert (`search`, `chat_id`, `session_type`, `start`, `end`, `status`)
* Hardening: Eingehende Filterwerte in der Session-Log-Abfrage weiterhin explizit sanitisiert verarbeitet
* Wartung: Vergleichbare Admin-Tabellenpruefungen auf gleiches Muster durchgefuehrt (keine weiteren Treffer)
* Fix: Chat-Status-Wechsel (Profil + Toolbar) wieder persistent; Status bleibt nach Seitenwechsel/Reload erhalten
* Fix: Legacy-AJAX fuer `chat_update_user_status` auf korrektes Nonce-Feld vereinheitlicht
* Fix: User-Status wird beim Speichern zusaetzlich in `psource-chat-user` synchronisiert (Kompatibilitaet Alt/Neu-Meta)
* Fix: Adminbar-Statusumschalter zeigt wieder alle verfuegbaren Stati (inkl. `away`/Offline)
* Fix: CP-Community-Avatar-Kompatibilitaet wiederhergestellt (`user_avatar_core_avatar_paths`)
* UI: Statusfarben vereinheitlicht (`available`=gruen, `unavailable`=gelb, `away`=rot), inkl. CSS-Fallback wenn gelbe Icon-Datei fehlt

= 1.1.1 =

* ClassicPress-sicher: jQuery UI Tabs/Datepicker entfernt, native Tabs & Datumsfelder
* Admin-UI: Tabs neu gestylt, Farbwähler standardmäßig als Swatch, Session-Logs mit native date
* Editor: Chat-Button in die Medienleiste verschoben, TinyMCE-Abhängigkeit entfernt, Modal-Tabs repariert
* Security: Nonce-Pruefung fuer schreibende Chat-Aktionen verstaerkt (inkl. `chat_messages_update`)
* Security: Eingaben fuer Session-/Filter-Parameter zusaetzlich sanitisiert; SQL-Abfragen in Session-Logs gehaertet
* Security: Link-Preview gegen SSRF gehaertet (unsichere Hosts/IP-Bereiche geblockt)
* Performance: Session-Log-Liste optimiert (N+1-Abfragen fuer Teilnehmer reduziert, Active-User gecacht)
* Hardening: Ausgaben in Pop-out-Template und Admin-Log-Spalten konsequent escaped

= 1.1.0 =

* Emoji-Picker: Modal im Chatfenster, kompaktere Emojis, Suchfeld, Tabs
* Sendebutton: Option rechts als Icon, unten weiterhin Text, DOM-Logik korrigiert
* Fehlerbehebung: 500er im Poll behoben (Filter-Parameter angepasst)
* Cleanup: Debug-Logs im JS entfernt; Produktions-Logging wieder deaktiviert

= 1.0.0 =

* Veröffentlichung


