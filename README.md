# PS Chat

**Deutsch** | [English](README.en.md)

[![Version](https://img.shields.io/badge/Version-1.1.5-2271b1?style=flat-square)](readme.txt)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777bb4?style=flat-square&logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-bis%207.1.0-21759b?style=flat-square&logo=wordpress&logoColor=white)
![ClassicPress](https://img.shields.io/badge/ClassicPress-2.7.4-03768e?style=flat-square)
[![Lizenz](https://img.shields.io/badge/Lizenz-GPL--2.0--or--later-2ea44f?style=flat-square)](https://www.gnu.org/licenses/gpl-2.0.html)

**Bring Deine Community ins Gespräch.**

Mit **PS Chat** integrierst Du leistungsfähige Echtzeit-Chats direkt in Deine ClassicPress- oder WordPress-Website.

Erstelle themenspezifische Chat-Räume, aktiviere einen dauerhaft sichtbaren Corner-Chat für Deine Community oder nutze Chat-Sitzungen für Support, Beratung und Live-Veranstaltungen.

Ob Community, Support, Social Network oder einfach ein Ort für den direkten Austausch – **Du entscheidest, wie und wo Deine Nutzer miteinander chatten.**

## Chat, wo Du ihn brauchst

PS Chat bietet verschiedene Möglichkeiten, Chats auf Deiner Website bereitzustellen:

* 💬 **Corner Chat** – dauerhaft sichtbarer Chat im Frontend
* 📄 **Seiten- und Beitrags-Chat** – themenspezifische Chat-Räume direkt in Deinen Inhalten
* 🧩 **Chat-Widget** – Chat in jedem verfügbaren Widget-Bereich
* 👥 **BuddyPress-Gruppenchat** – eigener Chat für einzelne Gruppen
* 🔒 **Private Chats** – direkte Gespräche zwischen verbundenen Nutzern

Du kannst mehrere Chat-Instanzen gleichzeitig betreiben – auch auf derselben Seite.

### Mobile Floating-Chats

Bis einschließlich **768 Pixel** werden Corner-, Netzwerk- und private Chats
standardmäßig in einem gemeinsamen Fenster mit horizontal scrollbaren Tabs
angezeigt – im Frontend ebenso wie im ClassicPress-/WordPress-Admin.
Nur der aktive Chat ist sichtbar. Neue private Sitzungen erscheinen automatisch
als weitere Tabs; ungelesene Nachrichten werden am jeweiligen Tab gezählt und
beim Öffnen zurückgesetzt.
Der aktive Chattitel bleibt im kompakten mobilen Header unterhalb der Tabs
sichtbar; die Chat-Aktionen stehen rechts daneben.

Unter **Frontendchat → Position** lässt sich die mobile Gruppierung abschalten
(`mobile_chat_grouping`: `tabs` oder `disabled`) oder der Breakpoint ändern
(`mobile_chat_grouping_breakpoint`: 320–1024 Pixel, Standard 768).
Die Defaults gelten auch für bestehende Installationen. Wird ein Netzwerk-Chat
angezeigt, gelten die entsprechenden Einstellungen des Netzwerk-Chats.

Der gemeinsame Minimieren-Button merkt sich den zuletzt aktiven Tab.
Die individuellen Min/Max-Einstellungen und vorhandenen Benutzer-Cookies bleiben
für die separaten Desktopfenster erhalten. Oberhalb des Breakpoints werden die
ursprünglichen Chat-Elemente an ihre Position zurückverschoben, nicht kopiert.
In der Tab-Leiste bewegen **Links/Rechts** und **Home/End** den Fokus;
**Enter/Leertaste** öffnet den gewählten Chat.

## Privater Gast-Support mit PS Support

Der neue Supportchat ist **vollständig von öffentlichen Räumen und normalen
privaten Mitglieder-Chats getrennt**. Besucher brauchen kein Benutzerkonto.
Jeder Browser erhält eine eigene Unterhaltung; andere Besucher sehen weder
Nachrichten noch Kontaktangaben.

1. **PS Support aktivieren** und dort Kategorien mit einem Bearbeiter oder einem
   Team konfigurieren.
2. Unter **Netzwerkverwaltung → Support → Einstellungen → Supportchat** den
   Netzwerk-Supportchat aktivieren und den Chatnamen festlegen.
   Auf der in PS Support eingestellten zentralen Support-Seite erscheint er
   automatisch, sofern PS Chat dort aktiv ist.
3. Optional **Subsites dürfen Supportchats nutzen** freigeben.
   Auf einer Subsite muss zusätzlich PS Chat aktiv sein und unter
   **Support → Einstellungen → Supportchat** eigener oder Netzwerk-Support
   gewählt werden. Ohne Freigabe oder bei lokaler Deaktivierung bleibt er aus.
   Eigener Chat-Support benötigt keine aktivierte Ticketfunktion.
   Beim Netzwerk-Support werden Pflichtangaben, Besucher-Hinweis, Fristen und
   Ansprechpartner zentral übernommen; Chatname und Platzierung bleiben lokal.
4. Unter **Wer erhält Anfragen ohne zuständigen Bearbeiter?** mindestens einen
   Support-Mitarbeiter mit Namen auswählen. Die Suche nach Name oder
   Benutzername liefert ab zwei Zeichen höchstens 20 Vorschläge. Ein Klick fügt
   die Person dem sichtbaren Team hinzu; „Entfernen“ nimmt sie wieder heraus.
   Berechtigt sind Mitarbeiter der konfigurierten Support-Rollen, Administratoren
   und ausdrücklich zugewiesene Kategorie-Teams der jeweiligen Support-Ebene.
   Bei CRM-Anbindung gilt ausschließlich der bestehende CRM-Agentenpool.
   Normale Website-Benutzer werden nicht automatisch angeboten. Die Prüfung gilt
   auch beim Speichern und bei der Empfängerermittlung für neue Chats.
5. Ein schwebendes Support-Icon, den Shortcode `[ps_chat_support]` oder das
   Widget **PS Chat: Privater Support** verwenden. Eine Einbettung unterdrückt
   auf derselben Seite weitere Instanzen und das schwebende Icon.

Unter **Hinweis für Besucher** kann der Administrator einen eigenen kurzen Text
zum Ablauf oder zu Antwortzeiten verfassen. Das Feld ist standardmäßig leer:
Es werden keine vorgegebenen Erklärungen zu Cookies, Fristen oder internen
Abläufen in das Besucherfenster eingeblendet. Beim Netzwerk-Support gilt der
Hinweis der Netzwerkverwaltung, beim eigenen Support der lokale Text.
Der Hinweis erscheint nur vor Beginn einer Unterhaltung.

Ein optionaler **Support-Bot** wird in PS Support unter **Einstellungen →
Support-Bot** gepflegt. Im Chat können Besucher ein Thema auswählen und dessen
Hilfe lesen, ohne bereits eine Sitzung oder E-Mail auszulösen. Bei Übergabe
werden Kategorie und eine bearbeitbare Zusammenfassung ins Anfrageformular
übernommen. Erst das ausdrückliche Absenden erstellt den privaten Chat.
Der direkte Weg zum Support bleibt erreichbar; laufende Chats werden nicht
durch den Bot unterbrochen.

Erst die **erste Nachricht** erzeugt eine Sitzung und benachrichtigt alle
zuständigen Kategorie-Bearbeiter einzeln per E-Mail. Die E-Mail enthält einen
Link, aber keine vertraulichen Nachrichten oder Gast-Kontaktangaben.
Mitarbeiter müssen sich regulär anmelden und **Chat übernehmen** anklicken.
Bei gleichzeitiger Übernahme gewinnt genau ein Mitarbeiter; nur dieser kann
antworten oder abschließen. Ein Linkaufruf allein übernimmt nichts.
Site-Administratoren können die Gespräche ihres eigenen Supports einsehen,
Superadministratoren alle Gespräche ihres Netzwerks. Sonst gelten die beim
Start gespeicherten Kategorie-/Ersatz-Empfänger und der zugewiesene Bearbeiter;
lokale Mitarbeiter müssen weiterhin Mitglied der zuständigen Site sein.
Eine spätere Kategorieänderung verteilt laufende Chats nicht neu.

Der Posteingang **Support → Supportchats** ist außerdem ohne E-Mail-Link verfügbar.
Es gibt keinen eigenen Supportchat-Hauptmenüpunkt. Einstellungen werden in
PS Support verwaltet und bleiben auch bei inaktivem PS Chat erreichbar;
der Posteingang weist dann auf das fehlende Plugin hin.
Fehlgeschlagene E-Mail-Benachrichtigungen werden angezeigt und können durch
berechtigte Mitarbeiter erneut gesendet werden. Erfolgreicher Mailversand ist
keine Zustellgarantie und keine Online-Anzeige; eine sofortige Antwort wird
nicht versprochen. Es erfolgt **keine automatische Ticketanlage**.

### Gastzugang und Aufbewahrung

- Name und E-Mail sind standardmäßig freiwillig; der Administrator kann beides
  unabhängig verpflichtend machen. Eine E-Mail-Adresse wird nicht verifiziert.
- Ein zufälliges **HttpOnly-/SameSite-Strict-Cookie** gewährt Zugriff auf die
  Sitzung, standardmäßig **168 Stunden** (1–2160 konfigurierbar).
  HTTPS ist im Produktivbetrieb erforderlich. Gelöschte Cookies, ein anderer
  Browser oder das Ablaufdatum verhindern die Wiederaufnahme; ein öffentlicher
  Sitzungs-Identifier allein gewährt keinen Zugriff.
- Nachrichten und Kontaktangaben werden standardmäßig **30 Tage ab Beginn**
  aufbewahrt (1–365 Tage, mindestens so lang wie der Browser-Zugang).
  Die stündliche WP-Cron-Bereinigung entfernt danach Sitzung und Nachrichten.
  Für verlässliche Ausführung einen System-Cron einrichten. Fristen gelten
  jeweils ab Beginn und werden durch Nachrichten nicht verlängert; geänderte
  Einstellungen gelten nur für neue Sitzungen.
- Geschlossene Chats können bis zum Ablauf im ursprünglichen Browser gelesen
  werden. Der Gast kann anschließend eine neue, getrennte Anfrage beginnen.
- Gesprächsinhalte werden als Klartext gespeichert und sicher als Text
  dargestellt, ohne öffentliche Chat-Filter oder Medien-/Upload-Verarbeitung.
  Ein Gast ist eine Browser-Identität, kein verifiziertes Benutzerkonto.
- Startversuche sind auf fünf pro IP und Netzwerk pro Stunde, Nachrichten auf
  30 pro Sitzung/Autor und Minute begrenzt. Die Ratenbegrenzung speichert
  zeitlich begrenzte HMAC-Schlüssel, keine Klartext-IP-Adressen.

Die REST-Routen liegen unter `/psource-chat/v1/support/`. Gastzugriff wird
über das Browser-Cookie geschützt, schreibende Gastzugriffe zusätzlich durch
Origin-Prüfung. Mitarbeiter verwenden normale Anmeldung und REST-Nonce;
jede Unterhaltung wird zusätzlich nach Support-Ebene und Zuständigkeit geprüft.
Private Antworten sind nicht cachebar. Tabellen:
`{base_prefix}ps_chat_support_sessions`, `ps_chat_support_messages` und
`ps_chat_support_limits`. Die Daten sind nicht Ende-zu-Ende-verschlüsselt:
berechtigte Administratoren und Betreiber mit Datenbankzugriff können sie lesen.
Datenschutzhinweise und serverseitige Backups entsprechend berücksichtigen;
Backups werden durch die Chat-Bereinigung nicht verändert.

### Integration testen

Auf einer **wegwerfbaren Testinstallation** mit aktiven Plugins:

```sh
PS_CHAT_SUPPORT_TEST=1 wp eval-file tests/support-chat-integration.php
wp eval-file ../ps-support/tests/category-teams.php
```

Der Supportchat-Test prüft echte REST-Berechtigungen und Datenbankzugriffe,
unter anderem Browser-Isolation, gefälschte Tokens, Origin, Team-Übernahme,
Antwortrechte, Wiederholungen, Mailfehler und Bereinigung. Test-Mails werden
abgefangen; Benutzer, Sitzungen und geänderte Einstellungen werden zurückgebaut.

## Passt sich Deiner Website an

![PS Chat Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-settings.jpg)

*Passe das Erscheinungsbild von PS Chat an das Design Deiner Website an.*

PS Chat lässt sich umfangreich an Dein Theme und Deine Website anpassen.

Für verschiedene Chat-Typen stehen eigene Gestaltungsmöglichkeiten zur Verfügung. Du kannst unter anderem festlegen:

* Position
* Größe
* Rahmen
* Farben
* Schriftart
* Schriftgröße
* Buttons
* Abstände
* Benutzerlisten
* Avatare
* Zeit- und Datumsanzeigen
* Emoticons
* Systemnachrichten

Damit kannst Du jeden Chat optisch an Deine Website anpassen.

## Benutzerrollen und Zugriffskontrolle

![PS Chat auf einer Seite](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-page.jpg)

*Erstelle themenspezifische Chat-Räume direkt auf Seiten und Beiträgen.*

Du entscheidest, wer Deine Chats sehen und verwenden darf.

Öffne einen Chat für alle Besucher oder beschränke ihn auf eingeloggte Nutzer und bestimmte Benutzerrollen.

Damit eignet sich PS Chat beispielsweise für:

* öffentliche Community-Chats
* Mitgliederbereiche
* interne Team-Kommunikation
* Support-Sitzungen
* Kundenbereiche
* private Veranstaltungen

## Chat-Verlauf und Moderation

![PS Chat Logs](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/logs.jpg)

*Speichere Chat-Verläufe und greife auch später wieder darauf zu.*

Gerade für Support und Community-Kommunikation ist ein Chat-Verlauf besonders praktisch.

Mit den integrierten Moderationsfunktionen kannst Du:

* Chat-Verläufe speichern
* vergangene Unterhaltungen einsehen
* einzelne Nachrichten ausblenden
* Benutzer sperren
* Benutzer anhand ihrer E-Mail-Adresse blockieren
* IP-Adressen blockieren
* bestimmte Wörter sperren
* eigene Meldungen für blockierte Inhalte festlegen

So behältst Du auch bei größeren Communities die Kontrolle über Deine Chats.

## BuddyPress und soziale Netzwerke

![PS Chat Benutzerstatus](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-available-735x470.jpg)

*Zeige anderen Nutzern, ob Du gerade online bist.*

PS Chat lässt sich hervorragend mit **BuddyPress** kombinieren.

Gruppen können ihren eigenen Live-Chat erhalten, der ausschließlich den jeweiligen Gruppenmitgliedern zur Verfügung steht.

Zusätzlich können Nutzer ihren Online-Status anzeigen und – abhängig von der Konfiguration – direkt mit ihren Freunden chatten.

## Chat von überall

![PS Chat Fenster](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/chat-window-735x4701.jpg)

*Platziere Deinen Chat dort, wo Deine Nutzer ihn benötigen.*

Du kannst PS Chat praktisch überall auf Deiner Website einsetzen:

* im Frontend als Corner Chat
* im Backend
* innerhalb von Beiträgen
* innerhalb von Seiten
* in Custom Post Types
* in Widget-Bereichen
* innerhalb von BuddyPress-Gruppen

Damit kannst Du beispielsweise für einen Film, ein Event, ein Produkt oder einen Artikel einen eigenen thematischen Chat bereitstellen.

## Mehrere Chat-Instanzen

PS Chat ist nicht auf einen einzigen Chat beschränkt.

Du kannst mehrere Chat-Instanzen gleichzeitig betreiben und jede davon individuell konfigurieren.

Beispielsweise:

* einen öffentlichen Community-Chat
* einen Support-Chat
* einen Mitglieder-Chat
* eigene Chats für bestimmte Beiträge
* Gruppen-Chats für BuddyPress
* private Unterhaltungen zwischen Nutzern

## Chat direkt im Editor einfügen

Für Seiten- und Beitrags-Chats stellt PS Chat eine eigene Editor-Integration bereit.

![PS Chat Editor Button](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-button.png)

Du kannst festlegen, für welche Beitragstypen die Chat-Funktion im Editor verfügbar sein soll.

Beim Bearbeiten eines entsprechenden Inhalts steht anschließend ein Chat-Button im Editor zur Verfügung.

![PS Chat Editor Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-modal.png)

Mit wenigen Klicks kannst Du einen individuellen Chat einfügen und dessen Einstellungen festlegen.

Das ist besonders praktisch für:

* Film- und Serien-Datenbanken
* Event-Portale
* Produktseiten
* Wissensdatenbanken
* Community-Projekte
* Gaming-Websites
* Diskussionsseiten

## Chat als Widget

![PS Chat Widget](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-widget.png)

Du möchtest einen Chat in Deiner Sidebar oder einem anderen Widget-Bereich anzeigen?

Kein Problem.

Füge einfach das **PS Chat Widget** über die Widget-Verwaltung hinzu und konfiguriere den Chat direkt dort.

## BuddyPress-Gruppenchat

![PS Chat BuddyPress Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-settings.png)

PS Chat integriert sich direkt in **BuddyPress-Gruppen**.

Gruppenadministratoren können den Gruppenchat für ihre Gruppe aktivieren.

Der Chat ist anschließend ausschließlich für Mitglieder dieser Gruppe zugänglich.

![PS Chat BuddyPress Gruppenchat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-chat-2.png)

Sobald der Chat aktiviert wurde, können alle Mitglieder der Gruppe an den Gesprächen teilnehmen.

## Private Nachrichten

![PS Chat privater Chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-private-chat.png)

Nutzer können private Gespräche mit anderen online befindlichen Nutzern starten.

In Verbindung mit BuddyPress können private Chats direkt aus den Freundeslisten heraus gestartet werden.

![PS Chat BuddyPress privater Chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-private-buddypress1.png)

## Online-Status

![PS Chat Online-Status](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-online-status.png)

Eingeloggte Nutzer können ihren Online-Status direkt über die WordPress-Toolbar verwalten.

So sehen andere Mitglieder auf einen Blick, wer gerade verfügbar ist.

Die verfügbaren Funktionen können von jedem Nutzer über das eigene Profil angepasst werden.

![PS Chat Benutzerprofil](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-user-profile-settings.png)

## Allgemeine Einstellungen

PS Chat bietet zentrale Einstellungen, die für alle Chat-Instanzen gelten, sowie individuelle Optionen für die einzelnen Chat-Typen.

### Polling

Lege fest, wie häufig PS Chat nach neuen Nachrichten sucht und welche Methode dafür verwendet werden soll.

![PS Chat Polling](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-polling.png)

### Moderation

Konfiguriere globale Moderationsregeln für Deine Chats.

![PS Chat Moderation](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-moderation.png)

Du kannst unter anderem Benutzer über ihre IP-Adresse oder E-Mail-Adresse blockieren und bestimmte Wörter sperren.

### URL-Steuerung

Lege fest, auf welchen Seiten und URLs Chat-Instanzen angezeigt oder ausgeblendet werden dürfen.

![PS Chat URL-Steuerung](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-url-blocking.png)

Die Einstellungen können sowohl das Frontend als auch den Administrationsbereich betreffen.

### WordPress-Administration

Definiere Standardwerte für Nutzer, die ihre persönlichen Einstellungen noch nicht angepasst haben.

![PS Chat WP-Admin Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-wpadmin.png)

### BuddyPress

Wenn BuddyPress aktiviert ist, stehen zusätzliche Einstellungen für Gruppen-Chats zur Verfügung.

![PS Chat BuddyPress Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-common-buddypress.png)

## Corner Chat

Der Corner Chat ist ideal, wenn Deine Community jederzeit miteinander kommunizieren können soll.

![PS Chat Corner Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-bottom-corner.png)

Du kannst ihn sowohl im Frontend als auch im Administrationsbereich aktivieren und einen individuellen Titel vergeben.

### Erscheinungsbild

![PS Chat Erscheinungsbild](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-appearance.png)

Passe Farben, Größen, Schriften und weitere Elemente an Dein Website-Design an.

### Position

![PS Chat Position](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-position.png)

Der Chat muss nicht zwangsläufig unten rechts erscheinen.

Du kannst ihn unter anderem positionieren:

* oben links
* oben rechts
* unten links
* unten rechts

Zusätzlich kannst Du Abstände zu den Seitenrändern festlegen.

### Nachrichten

![PS Chat Nachrichten](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-2042-settings-site-messages.png)

Lege fest, wie Nachrichten dargestellt werden.

Unterstützt werden unter anderem:

* Avatare
* Benutzernamen
* Zeitstempel
* Datumsangaben
* Emoticons

### Benutzerliste

![PS Chat Benutzerliste](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-site-users-list.png)

Zeige eine Liste der aktuell verfügbaren Nutzer an und bestimme deren Position und Darstellung.

Du kannst zwischen Avataren und Benutzernamen wählen.

### Authentifizierung

![PS Chat Authentifizierung](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-site-authentication.png)

Lege fest, welche Anmeldemethoden für den jeweiligen Chat verfügbar sind.

Zusätzlich kannst Du festlegen:

* welche Informationen nicht eingeloggten Besuchern angezeigt werden
* welche Benutzerrollen als Moderatoren fungieren dürfen
* welche Authentifizierungsmethoden verwendet werden

### Erweiterte Einstellungen

![PS Chat Erweiterte Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-site-advanced.png)

Mit den erweiterten Einstellungen kannst Du unter anderem:

* die Anzahl angezeigter Nachrichten begrenzen
* individuelle Sitzungsnachrichten definieren
* bestimmte URLs vom Chat ausschließen

## Seiten- und Beitrags-Chats

Für Chats innerhalb von Seiten und Beiträgen stehen zusätzliche Einstellungen zur Verfügung.

![PS Chat Editor-Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-settings.png)

Über die Einstellungen kannst Du festlegen, für welche Beitragstypen der Chat-Button im Editor zur Verfügung steht und welche Benutzer ihn verwenden dürfen.

Das ist besonders praktisch, wenn Du für bestimmte Inhalte eigene Diskussionsräume anbieten möchtest.

![PS Chat Editor Button](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-button.png)

![PS Chat Editor Dialog](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-settings-page-wysiwyg-modal.png)

## BuddyPress-Gruppen

![PS Chat BuddyPress Gruppen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-settings.png)

Jede BuddyPress-Gruppe kann ihren eigenen Chat erhalten.

Der Gruppenadministrator aktiviert den Chat direkt für die jeweilige Gruppe.

![PS Chat BuddyPress Gruppenchat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-buddypress-group-chat-2.png)

Der Chat ist anschließend nur für Mitglieder der jeweiligen Gruppe verfügbar.

## Benutzerdefinierte Einstellungen

Eingeloggte Nutzer können ihren Online-Status direkt über die WordPress-Toolbar festlegen.

![PS Chat Online-Status](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-online-status.png)

Zusätzlich können Nutzer persönliche Einstellungen für die Chat-Funktionen über ihr Profil verwalten.

![PS Chat Benutzerprofil-Einstellungen](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-user-profile-settings.png)

In Verbindung mit BuddyPress können Nutzer außerdem private Chats direkt über ihre Freundesliste starten.

![PS Chat privater BuddyPress-Chat](https://web.archive.org/web/20180316215143im_/https://premium.wpmudev.org/wp-content/uploads/2010/09/wordpress-chat-private-buddypress1.png)

## Leistungsfähigkeit und Server-Ressourcen

Echtzeit-Chat kann – insbesondere bei vielen gleichzeitig aktiven Nutzern – zusätzliche Serverressourcen benötigen.

PS Chat wurde darauf ausgelegt, die Belastung möglichst gering zu halten. Bei stark frequentierten Websites mit vielen gleichzeitig aktiven Chat-Instanzen solltest Du dennoch die verfügbaren Serverressourcen im Auge behalten.

Auf größeren Websites kann es sinnvoll sein, das für WordPress verfügbare PHP-Memory-Limit entsprechend anzupassen.

Beispielsweise:

```php
define( 'WP_MEMORY_LIMIT', '256M' );
```

Die Einstellung gehört in die `wp-config.php` und sollte vor der Zeile

```php
/* That's all, stop editing! Happy publishing. */
```

eingetragen werden.

Bei Shared Hosting mit vielen gleichzeitig aktiven Chat-Nutzern kann außerdem ein leistungsfähigeres Hosting-Paket erforderlich sein.

## PS Chat Features

### Chat-Typen

* Corner Chat
* Chat innerhalb von Beiträgen und Seiten
* Chat-Widget
* BuddyPress-Gruppenchat
* Private Chats
* Mehrere Chat-Instanzen gleichzeitig

### Darstellung

* Avatare
* Gravatar-Unterstützung
* Emoticons
* Chat-Verlauf
* Chat-Sounds
* Zeit- und Datumsanzeigen
* Individuelle Farben
* Individuelle Schriftarten
* Individuelle Schriftgrößen
* Frei konfigurierbare Fenstergrößen
* Frei konfigurierbare Positionierung
* Pop-out für Chat-Fenster
* Vollbildmodus für Tablets und Smartphones

### Benutzer und Zugriff

* WordPress-Login
* Benutzerrollen
* Moderatoren
* Online-Status
* Private Nachrichten
* BuddyPress-Integration
* Individuelle Zugriffskontrolle

### Moderation

* Chat-Verläufe
* Nachrichten moderieren
* Benutzer per E-Mail-Adresse sperren
* Benutzer per IP-Adresse sperren
* Bestimmte Wörter blockieren
* Individuelle Moderationsmeldungen
* URL-basierte Anzeigeeinschränkungen

### Integration

* ClassicPress
* WordPress
* WordPress Multisite
* BuddyPress
* BuddyPress-Gruppen
* Widgets
* Beiträge
* Seiten
* Custom Post Types

## Kompatibilität

PS Chat wird als **PSOURCE-Projekt** für ClassicPress und WordPress weiterentwickelt und gepflegt.

Das Plugin kann sowohl auf **ClassicPress** als auch auf **WordPress** eingesetzt werden.

## Installation

1. Lade PS Chat herunter.
2. Entpacke das Plugin.
3. Lade den Plugin-Ordner nach `/wp-content/plugins/` hoch.
4. Aktiviere **PS Chat** über die Plugin-Verwaltung.
5. Öffne anschließend den neuen Menüpunkt **Chat** im Administrationsbereich.
6. Konfiguriere die allgemeinen Einstellungen.
7. Erstelle Deinen ersten Chat.

Bei einer Multisite-Installation kann PS Chat netzwerkweit aktiviert werden. Die einzelnen Websites verwalten ihre Chat-Instanzen unabhängig voneinander.

## Hinweise

Bei Problemen mit der PHP-Ausführung durch Sicherheitsplugins kann es erforderlich sein, die verwendete Polling-Methode anzupassen.

Prüfe in diesem Fall unter:

**Chat → Einstellungen → Allgemein → Polling**

die Einstellung für die Polling-Quelle.

Falls **Plugin AJAX** Probleme verursacht, kann **WordPress AJAX** als alternative Methode verwendet werden.

---

## PSOURCE

**Open Source aus dem Weserbergland.**

PS Chat ist Teil der **PSOURCE**-Plugin-Sammlung und wird mit dem Ziel weiterentwickelt, leistungsfähige und vollständig nutzbare Open-Source-Lösungen für ClassicPress und WordPress bereitzustellen.

**Keine abgespeckte Version. Keine künstlichen Einschränkungen. Einfach Software, die ihren Job macht.**
