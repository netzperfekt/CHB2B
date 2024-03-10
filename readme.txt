
--------------------------
Carl Henkel B2B-Funktionen
--------------------------
netzperfekt, N. Ehnert <info@netzperfekt.de>

---------
Changelog
---------
1.0.0   07.12.23    Initiale Version

----------------------------
  WICHTIG ACHTUNG WICHTIG
----------------------------

- Unter Caches / Performance / Einstellungen / Allgemein / HTTP-Cache muss der Eintrag "frontend/detail"
    ENTFERNT werden, damit diese Seiten nicht mehr gecacht werden. Nur so kann die Option "Nicht bestellbar"
    angezeigt werden, wenn ein Anforderungs-Warenkorb in Prüfung ist.
    [ein selektives Abschalten des Caches im Plugin erscheint nicht möglich zu sein,
    insgesamt sollte das im B2B-Umfeld keine größeren Geschwindigkeitseinbußen ergeben]

----------------------------
Technische Kurzdokumentation
----------------------------

- Das Plugin rüstet B2B-Funktionen für SW 5 nach. Es können Nutzer definiert werden, die entweder bis zu einem
  festgelegten Bestell-Budget eigenhändig Bestellungen durchführen, oder alternativ Bestellungen zur Freigabe
  anfordern können. Freigeber können diese Bestellungen einsehen, zurückweisen oder letztlich bestellen.

- Das Plugin basiert auf einem rollenbasierten Rechtesystem (Berechtigungen -> Rollen -> Nutzer),
  diese werden über die Import-Schnittstelle zugeordnet. Eine Bearbeitung im Backend o.ä. ist derzeit nicht vorgesehen.
  Folgende Rollen und Berechtigungen sind derzeit vorgesehen:

    Berechtigungen:
        Zusammenstellen (request)
        Bestellen mit Budget (request_budget)
        Bestellen ohne Limit (order_unlimited)
        Bestellen fremde WK (approve)
        Warenkorb löschen (delete_cart)
        Nutzer auflisten (show_user)
        Konten anfordern (request_new_user)
        Änderungen Nutzer veranlassen (request_changes)

    Rollen:
        Zusammensteller (requester)
        Bestellen mit Budget (requester_budget)
        Freigeber (approver)
        4-Augen (two_person)
        Hauptnutzer (main)
        Admin (admin)

- Weitere Anpassungen wurden in der CH-Importschnittstelle vorgenommen (engine/Shopware/Plugins/Local/Frontend/ChImport),
  um die Zuordnung von Nutzern zu Rollen und das Bestellbudget zu realisieren.

- Die Zuordnung zu einer Firma erfolgt implizit über die Haupt-Kundennummer (Freitextfeld beim Kunden).
  Jeder Nutzer hat zusätzlich noch eine Unter-Kundennummer (z.B. 888888.1)

- Bei der Installation des Plugins werden folgende Datenbank-Tabellen angelegt und - falls noch nicht vorhanden -
  mit Inhalten befüllt:
    - chb2b_order_basket / chb2b_order_basket_attributes
    - chb2b_permission / chb2b_role / chb2b_role_permission / chb2b_user_role

- Die Templates für die E-Mailbenachrichtigungen werden über standardmässige SW 5-Mailtemplates abgebildet,
  bei der Plugin-Installation werden die Inhalte aus dem Plugin-Verzeichnis /templates/* eingelesen.
  Die E-Mailadresse für Mails an Carl Henkel kann in den Plugin-Settings definiert werden.

- Grundsätzlich wird der Bestellablauf von SW 5 nicht verändert, die Produkte werden über normale SW-Warenkörbe
  bestellt. Das Plugin greift jedoch in den "Jetzt kaufen"-Button ein und fügt - je nach Berechtigungen -
  die Bestell-Anforderung hinzu.

- Weiterhin werden zwei Bereiche im Kundenaccount hinzugefügt (Anforderungen und Nutzerübersicht)

- Funktionen im Berreich "Nutzerübersicht" (Nutzer ändern, löschen, hinzufügen) erzeugen ausschließlich E-Mails
  an Carl Henkel, die weitere Verarbeitung/Steuerung erfolgt dann über die Import-Schnittstelle. Das Plugin selbst
  verändert keine Nutzerdaten.
