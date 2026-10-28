=== PS Chat ===
Contributors: DerNerd (PSOURCE)
Tags: multisite, chat, community, messenger, support
Requires at least: 5.0
Requires CP: 2.7
Tested up to: 7.1.0
ClassicPress: 2.7.4
Stable tag: 1.1.4
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Erlaube Deinen Usern, mit Dir oder anderen Usern zu chatten. Inkl. Gruppenchats. Keine Drittanbieter, keine externen Abo-Kosten!

== Description ==

# PS Chat – Selbstgehosteter ClassicPress‑Chat

PS Chat ist ein leistungsfähiges, komplett selbstgehostetes Chat‑Plugin für ClassicPress. Es bietet Direktnachrichten, Gruppen‑Chats (BuddyPress), Medien‑Einbettungen und eine moderne Emoji‑Auswahl – ohne externe Dienste.

## Highlights

- Chat in Beiträgen/Seiten, Widgets oder als Dock unten rechts
- Private Chats zwischen angemeldeten Nutzern (Einladungen, Re‑Open)
- PS Community‑Gruppen‑Chats mit Moderation
- Medienunterstützung: Link‑Previews, Bilder, YouTube
- Moderner Emoji‑Picker (Suche, Kategorien, kompaktes Grid)
- Dateiuploads mit Vorschau (optional)
- Transienten‑Caching, optimierte Polling‑Routen
- Mehrsprachigkeit (Deutsch, EN, FR, IT)

## Funktionen im Detail

- Direktnachrichten: 1:1‑Chats, Einladungen, Status „available/busy/invisible“
- Gruppen: PS Community‑Gruppenräume inkl. Admin/Mod‑Rechten
- Medien: Automatische Erkennung und Rendering von URLs (Link‑Preview, Bild, Video)
- Emoji: Modal‑Picker im Chatfenster mit Suche und Tabs
- Uploads: Drag & Drop/Dateiwähler, saubere Einbettung in Nachrichten
- Moderation: Nachrichten ausblenden, IP‑Verwaltung, Benutzersperren
- Performance: Transienten, leichtgewichtige Polling‑Endpunkte

== ChangeLog ==

= 1.1.5 =
* Support-Bot: Optionale Themen-Selbsthilfe aus PS Support vor dem privaten Chat; Kategorie und Zusammenfassung bei ausdruecklicher Uebergabe uebernehmen, keine Benachrichtigung fuer reine Bot-Nutzung
* Supportchat: Empfaenger fuer neue Chats anhand vorhandener PS-Support-Mitarbeiterzuordnung pruefen; keine automatische Freigabe gewoehnlicher Website-Benutzer
* Feature: Privater Gast-Supportchat mit PS Support, getrennt von oeffentlichen Chatraeumen und ohne Benutzerkonto
* Integration: Pro Site deaktivierbar, eigener Support oder zentraler Netzwerk-Support; Kategorie-Bearbeiter und Teams erhalten einen authentifizierten Uebernahme-Link per E-Mail nach der ersten Nachricht
* Support: Exklusive, gegen gleichzeitige Uebernahmen abgesicherte Zuweisung; nur der zugewiesene Mitarbeiter antwortet oder schliesst den Chat
* UI: Eigenstaendiges schwebendes Support-Icon, Shortcode [ps_chat_support], Widget und responsiver Mitarbeiter-Posteingang
* Administration: Optionale Pflichtangaben fuer Name/E-Mail, konfigurierbare Browser-Zugangsfrist und automatische Datenloeschung sowie Ersatz-Ansprechpartner
* UI: Frei formulierbarer Besucher-Hinweis pro Support-Ebene statt fest eingeblendeter technischer Erklaerungen; standardmaessig leer
* Bedienung: Supportchat als Tab in den vorhandenen Support-Einstellungen; klare Aktivierung und Mitarbeiterauswahl nach Namen mit Suche statt Benutzer-IDs
* Mobil: Aktiver Chattitel und Aktionen in einer kompakten Headerzeile unterhalb der Tabs, damit mehr Platz fuer Nachrichten bleibt
* Integration: Posteingang und Einstellungen unter Support statt eigenem Hauptmenue; Netzwerkchat automatisch auf der konfigurierten Support-Site, Subsites nur mit Netzwerkfreigabe, aktivem PS Chat und lokaler Aktivierung
* Fix: Schnelles Minimieren/Wiederoeffnen erhaelt Entwuerfe und Kategorien; veraltete Polling-Antworten ueberschreiben keine neueren Chat-Aktionen
* Datenschutz: Zufallsbasierter HttpOnly-/SameSite-Gastzugang, sitzungsbezogene Rechtepruefung, Origin-Pruefung, Ratenbegrenzung und keine vertraulichen Chat-Inhalte in Benachrichtigungs-Mails
* Feature: Optionale mobile Gruppierung von Site-, Netzwerk- und privaten Floating-Chats in einem gemeinsamen Fenster mit Tabs im Frontend und Admin
* Einstellungen: Mobile Gruppierung standardmaessig aktiviert, auch fuer bestehende Installationen; Breakpoint standardmaessig 768 Pixel, konfigurierbar zwischen 320 und 1024 Pixeln
* UI: Horizontal scrollbare Tabs mit Chat-Titeln und Ungelesen-Zaehlern; dynamisch hinzugefuegte private Chats werden automatisch registriert
* UI: Gemeinsames Minimieren und Wiederherstellen des zuletzt aktiven Tabs mit separaten mobilen Cookies; individuelle Desktop-Min/Max-Einstellungen bleiben erhalten
* Barrierefreiheit: Tab-/Panel-Zuordnung per ARIA, Tastaturbedienung mit Pfeiltasten, Home/End und Enter/Leertaste, sichtbarer Fokus und Touch-Ziele ab 44 Pixeln
* Fix: Wechsel zwischen Mobil- und Desktopbreite stellt die urspruenglichen Chat-Elemente ohne duplizierte Inhalte wieder her
* Wartung: Validierung der neuen Site-/Netzwerk-Optionen und Cache-Busting fuer die Chat-Stylesheets

= 1.1.4 =
* Feature: Eingabemodus pro Chat-Instanz zwischen Klartext und kompakter Markup-Leiste waehlen
* Feature: Markup-Schaltflaechen fuer Fett, Kursiv, Code und Links ohne TinyMCE-Abhaengigkeit im Chat
* Kompatibilitaet: Markup-Nachrichten werden ueber Legacy-AJAX und moderne REST-Endpunkte einheitlich verarbeitet
* UI: Emoji, Dateiupload und Ton bei aktiver Markup-Leiste in einer gemeinsamen kompakten Werkzeugzeile gebuendelt
* UI: Zeichenzaehler verkleinert, rechtsbuendig angeordnet und ab 90 Prozent des jeweiligen Limits rot hervorgehoben
* UI: Namen sowie Datum/Uhrzeit in Nachrichten kompakter dargestellt und Moderator-Aktionen modernisiert
* Fix: Konfigurierte Chatbreiten werden auf kleinen Viewports wieder respektiert; schmale 200-Pixel-Chatboxen laufen nicht mehr aus dem Bildschirm
* Fix: Avatar-Klasse wird korrekt im Nachrichten-Markup gesetzt, damit Abstaende und Ausrichtung greifen
* Fix: Avatar-Aufloesung an die Adminbar angeglichen und um sichere CP-Community- sowie lokale Platzhalter-Fallbacks erweitert
* Fix: Zeichenzaehler aktualisiert sich auch bei Markup-, Emoji- und Upload-Eingaben sowie nach dem Senden zuverlaessig

= 1.1.3 =
* Fehler behoben der verhinderte das Chats geleert werden konnten
* Fix: Globale Einstellungs-Tabs repariert; Tab-Inhalte werden wieder im korrekten Panel angezeigt
* Fix: Defektes Panel-Markup in den globalen Einstellungen korrigiert (verhinderte leere/falsch verschachtelte Tabs)
* Fix: Emoji-Picker-Handling zwischen Legacy-Trigger und modernem Modal stabilisiert
* Fix: Emoji-Picker-Modal-Aufruf PHP-seitig kompatibel zum bestehenden Session-Aufruf gemacht
* Fix: Emoji-Picker-Positionierung an Trigger gebunden, inkl. robuster Fallback-Groesse (kein unsichtbares 0x0-Overlay mehr)
* Fix: Cache-Busting fuer Chat-Hauptscript verbessert (Datei-Zeitstempel), damit JS-Updates sofort greifen


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
