NÁVOD K NASAZENÍ – ODESÍLÁNÍ REZERVACÍ E-MAILEM
=================================================

1) NAHRAJTE NA SERVER (do stejné složky jako index.html):
   - send-reservation.php
   - config.php
   - celou složku phpmailer/ (i s podsložkou src/ a třemi soubory uvnitř)

   Výsledná struktura na serveru:
   /index.html
   /send-reservation.php
   /config.php
   /phpmailer/src/PHPMailer.php
   /phpmailer/src/SMTP.php
   /phpmailer/src/Exception.php

2) OTEVŘETE config.php A DOPLŇTE:
   - 'smtp_password' => heslo ke schránce restaurant@greengarden.al
     (přihlašovací údaje najdete/nastavíte v administraci Gigaserveru
     v sekci "E-maily")
   - Pokud chcete, aby rezervace chodily na jinou schránku než
     restaurant@greengarden.al, upravte 'to_email'.

3) OTESTUJTE:
   - Otevřete web, vyplňte rezervační formulář a odešlete.
   - Mělo by se objevit zelené potvrzení "Rezervimi u dërgua me sukses!"
     a e-mail by měl dorazit do schránky nastavené v 'to_email'.
   - Pokud se místo toho objeví okno s telefonním číslem
     ("Rezervimet online nuk janë...") a e-mail nedorazil, je chyba
     v nastavení SMTP – zkontrolujte heslo a údaje v config.php.
     Chybová hláška se zapisuje do PHP error logu na serveru
     (v administraci Gigaserveru, sekce logy/chyby).

4) BEZPEČNOST:
   - config.php obsahuje heslo ke schránce – nikde ho veřejně
     nesdílejte (např. na GitHubu) a pokud to hosting umožňuje,
     nastavte souboru práva 600.
   - Formulář má vestavěnou jednoduchou ochranu proti spam botům
     (honeypot pole "website", které je v HTML schované – žádný
     zásah není potřeba).

5) KOPIE HOSTOVI:
   - Host, který formulář vyplní, dostane automaticky kopii (CC)
     stejného e-mailu, který jde na restaurant@greengarden.al.
   - E-mail je teď napsaný jako interní oznámení pro restauraci
     ("Rezervim i ri nga faqja..."), takže host uvidí stejný text.
     Pokud chcete, aby host dostal jinak formulovanou, přívětivější
     zprávu (např. "Faleminderit për rezervimin..."), dejte vědět –
     šlo by to řešit odesláním dvou různých e-mailů místo CC.


Případné otázky ohledně napojení klidně směřuj zpátky do konverzace
s Claude.
