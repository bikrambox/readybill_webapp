<?php

return [

    // Übersetzungen ins Deutsche für die Desktop‑Version
    'title' => 'Wie lade ich Daten hoch?',
    'subtitle' => 'Um XLS- oder CSV-Daten einfach hochzuladen, haben wir einen Beispiel‑Datensatz \n                    <button type="button" class="btn btn-link m-0 p-0 downloadDataset">hier</button> \n                    den Sie herunterladen, nach Ihren Bedürfnissen bearbeiten und hochladen können.',

    'section1' => [
        'heading' => 'Wie lade ich Inventardaten im CSV- oder XLS-Format hoch?',
        'sub_heading' => 'XLS:',
        'para' =>[
            '1' => 'Sie können Ihre Inventarartikel einzeln über das Menü „<b>Inventar hinzufügen</b>“ hinzufügen. Dies ist praktisch, wenn es nur wenige Artikel sind, die Sie manuell nacheinander hinzufügen können, aber es ist keine gute Idee, wenn Sie hunderte Artikel auf einmal hochladen müssen.',
            '2' => 'Die Schaltfläche „<b>Daten hochladen</b>“ auf der Seite „<b>Inventar hinzufügen</b>“ ermöglicht es Ihnen, Inventardaten im CSV- oder XLS-Format hochzuladen. Sie müssen Ihre Daten lediglich im folgenden Format halten –',
            '3' => '<b>ARTIKELNAME:</b> Name des Artikels oder Produkts, das Sie hinzufügen. Es kann viele Produkte mit demselben Namen geben. Daher sollten Sie einen eindeutigen Namen vergeben, den Sie immer kennen. Zum Beispiel: Surf Excel 100, Surf Excel 500, Parle G Small, Parle G Large usw.',
            '4' => '<b>MENGE:</b> Nur wenn Sie Bestände verwalten, ist dieses Feld erforderlich oder obligatorisch. Wenn Sie Bestandsmengen verwalten, können Sie jederzeit den verfügbaren Bestand in Ihrem Geschäft angeben. <span class="text-danger"> Wenn Sie keine Bestände verwalten, können Sie dieses Feld leer lassen. Da dieses Feld optional ist, sofern Sie die Lagermenge nicht aktiviert haben, können Sie auch eine beliebige Zahl größer als 0 eingeben.</span>',
            '5' => '<b>Mindestbestand-Warnung:</b> Nur wenn Sie Bestände pflegen, können Sie dieses Feld nutzen, um sich zu warnen, wenn ein bestimmter Artikel unter Ihre Mindestbestandsmenge fällt. Wenn Sie beispielsweise ein schnellverkauftes Produkt „Amulya Powder 500“ haben und immer mindestens 10 Packungen auf Lager haben möchten, können Sie für dieses Produkt eine <b>Mindestbestand-Warnung</b> auf 10 einstellen. Dieses Feld ist nicht zwingend erforderlich.',
            '6' => '<b>UVP:</b> Die UVP ist der Marktpreis des Produkts, der normalerweise auf der Packung selbst gedruckt ist. Wenn Sie die UVP speichern möchten, müssen Sie „<b>Pflegen Sie die UVP?</b>“ im Menü <b>Voreinstellungen</b> aktivieren. Sie haben auch die Option, die UVP nicht auf dem Beleg oder der Rechnung anzuzeigen. Dies kann im Menü <b>Voreinstellungen</b> eingestellt werden.',
            '7' => '<b>VERKAUFSPREIS:</b> Der Verkaufspreis ist der Preis, zu dem Sie das Produkt verkaufen. Dies ist ein Pflichtfeld und für alle Zwecke erforderlich.',
            '8' => '<b>Einheit:</b> Jedes Produkt hat eine Einheit. Einheiten sind zum Beispiel – Beutel, Schachtel, Flasche, Stück, Dose, kg, Gramm usw. Zum Beispiel ist die Einheit von Maggi Tomatenketchup eine Flasche, während die Einheit von Good Knight ein Stück oder eine Packung sein kann. Die Liste aller Einheiten finden Sie auf der Seite „<b>Inventar hinzufügen</b>“. Einheit ist ein Pflichtfeld und immer erforderlich. Sie müssen die Einheit in ihrer abgekürzten Form verwenden, sonst wird das Hochladen nicht erfolgreich sein. Siehe unten stehende Tabelle für alle verfügbaren Einheiten und ihre Kurzformen.',
            '9' => '<b>HSN:</b> HSN steht für „Harmonized System of Nomenclature“. Dieser Code wird zur Klassifizierung von Produkten verwendet. Jedes Produkt hat seine eigene eindeutige HSN-Nummer. Die <b>Liste der HSN-Codes &amp; GST</b> finden Sie hier <a class="text-decoration-underline" href="https://cleartax.in/s/gst-hsn-lookup" target="_blank">https://cleartax.in/s/gst-hsn-lookup</a>. Sie können HSN über das Menü <b>Voreinstellungen</b> aktivieren und deaktivieren. Wenn Sie den HSN/SAC-Code deaktivieren, müssen Sie ihn nicht in Ihre Inventardaten eingeben.',
            '10' => '<b>GST:</b> Gemäß den Vorschriften der indischen Regierung ist die Goods and Services Tax verpflichtend. Jedem Artikel ist ein GST-Wert zugeordnet. Dies ist ein Pflichtfeld.',
            '11' => '<b>CESS:</b> Normalerweise haben Lebensmittelartikel keine CESS-Steuer, aber einige Produkte können eine CESS-Steuer haben. Wenn ein Produkt eine CESS-Steuer hat, müssen Sie den CESS-Betrag eingeben.',
            '12' => 'Unten steht ein Beispiel aus einem Teil einer XLS-Datei',
            '13' => 'Zum Beispiel: In diesem Fall verwaltet der Ladenbesitzer keine Lagermenge und der Mindestbestand kann nicht gepflegt werden. Daher hat der Ladenbesitzer diese Felder leer gelassen.',
            '14' => 'In der nachstehenden Tabelle bedeutet ein Feld mit <span class="text-danger">rotem Stern</span>, dass es immer obligatorisch ist.',
            '15' => 'Sowohl GST als auch CESS sind Steuern. Normalerweise ist CESS nicht erforderlich. In solchen Fällen müssen Sie eine Zahl zwischen 0 und 100 eingeben. Es werden nur Zahlen und Dezimalwerte akzeptiert.',
            '16' => 'Neben diesen Pflichtfeldern können Sie andere Felder über das Menü <b>Voreinstellungen</b> nach Bedarf als obligatorisch festlegen.',

            '18' => '<b>VAT:</b> Each product has a tax percentage. For example, <b>VAT (Value Added Tax)</b> may be applicable at rates such as 7% or 19%, depending on the product. You should enter the appropriate tax percentage for each item. THIS FIELD IS NOT MANDATORY. Even if there is no tax applicable, you will have to enter ‘0’.'  ### new entry v1 ###

        ]
    ],
    'section2' => [
        'heading' => 'CSV (durch Kommas getrennte Werte):',
        'para' => [
            '1' => 'Sie können Inventardaten auch als CSV-Datei hochladen. Das manuelle Erstellen einer CSV-Datei kann mühsam sein. Daher empfehlen wir Ihnen, die folgende Anleitung zu befolgen, um eine XLS-Datei einfach in eine CSV-Datei umzuwandeln.',
            '2' => 'Öffnen Sie die Excel-Datei',
            '3' => 'Wählen Sie Datei',
            '4' => 'Wählen Sie „Speichern unter“',
            '5' => 'Wählen Sie im Feld „Dateityp“ die Option CSV (durch Komma getrennt)',
            '6' => 'Wählen Sie einen Speicherort für die Datei',
            '7' => 'Klicken Sie auf Speichern',
            '8' => 'Alternativ können Sie eine beliebige Online-Anwendung verwenden, die Ihre Excel-Datei in eine CSV-Datei konvertiert.',
        ]

    ]

];