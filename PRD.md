**Product Requirements Document (PRD)**

**Projektname:** TYPO3 Extension "Bilder ohne Alt-Tag"
**Zielsystem:** TYPO3 v12 & v13

---

### 1. Zielsetzung

Diese Extension bietet ein Backend-Modul zur Erkennung von Bildern ohne gesetzten Alt-Text. Sie dient primär zur Prüfung der Barrierefreiheit und redaktionellen Qualitätssicherung von Medieninhalten im TYPO3-System.

---

### 2. Zielgruppe

- Redakteure
- Administratoren

---

### 3. Funktionsübersicht

#### 3.1 Bildliste ohne Alt-Text

- Anzeige aller Bilder im System, die **keinen Alt-Text** besitzen
- Berücksichtigung von:
  - Bildern im Fileadmin
  - Bilderreferenzen in `tt_content`, Content Blocks, und TCA-basierten Feldern
  - `sys_file_reference` und `sys_file_metadata`

#### 3.2 Definition "kein Alt-Tag"

- Bilder, deren Alt-Text leer ist (`""`) oder fehlt
- Es wird unterschieden zwischen:
  - **Alt-Text aus der Bildverwendung (FileReference)**
  - **Alt-Text aus dem Bild selbst (sys_file_metadata)**
- Optional konfigurierbar, ob ein Standard-Alt-Text aus den Metadaten als fallback akzeptiert wird

#### 3.3 Dekorative Bilder

- Bilder können als **dekorativ** markiert werden
- Markierung erfolgt z. B. über ein bool-Feld `is_decorative` im `sys_file_metadata`
- Dekorative Bilder werden **nicht mehr als fehlender Alt-Tag** gewertet
- Im Modul wird ein Hinweis angezeigt, ob ein Bild als dekorativ markiert wurde
- Optional: Im Modul selbst kann die Markierung gesetzt oder entfernt werden

#### 3.4 UI & Darstellung

- Backend-Modul mit Tabelle:
  - Vorschaubild
  - Dateiname & Pfad
  - Verwendungsort (Seiten-ID, Inhaltselement)
  - Alt-Text vorhanden (Ja/Nein)
  - Dekorativ (Ja/Nein)
- Filteroptionen:
  - Nach Storage (z. B. Dateispeicher)
  - Nach Sprache
  - Nach Seite
  - Nach "Dekorativ"-Status

#### 3.5 Export & Reporting

- CSV-Export aller angezeigten Datensätze
- Optional: Integration eines Scheduler Tasks zur periodischen Überprüfung

---

### 4. Technische Anforderungen

- Kompatibilität: TYPO3 12 LTS & 13
- PHP 8.1+
- Composer-Unterstützung
- Nutzung von Doctrine QueryBuilder oder TYPO3 DataHandlers
- Modularer Aufbau (PSR-4, Services, DI)

---

### 5. Sicherheit & Performance

- Zugriff auf das Modul nur für bestimmte Benutzergruppen (konfigurierbar per TSconfig)
- Skalierbar für große Datenmengen (Paginierung, Lazy Loading)

---

### 6. Sonstiges

- Extension-Key: `a11ly_companion`
- Lizenz: Open Source (MIT oder GPL v2)
- Dokumentation via README & In-Extension-Hilfe
