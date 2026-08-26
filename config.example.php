<?php
/**
 * Konfigurace odesílání e-mailů pro rezervační formulář Green Garden.
 *
 * DŮLEŽITÉ: tento soubor obsahuje citlivé přihlašovací údaje.
 * - Nikdy ho nezveřejňujte ve veřejném Git repozitáři.
 * - Nastavte na serveru práva 600 (jen vlastník může číst/zapisovat), pokud to hosting umožňuje.
 */

return [
    // --- SMTP přístup ke schránce, ZE KTERÉ se bude odesílat ---
    'smtp_host'     => 'mail.gigaserver.cz',
    'smtp_port'     => 465,              // 465 = SSL/TLS, 587 = STARTTLS
    'smtp_secure'   => 'ssl',            // 'ssl' pro port 465, 'tls' pro port 587
    'smtp_username' => 'restaurant@greengarden.al',   // přihlašovací jméno schránky (celá emailová adresa)
    'smtp_password' => 'ZDE_DOPLNIT_HESLO_KE_SCHRANCE',

    // --- Odesílatel (zobrazí se jako "od koho" e-mail přišel) ---
    'from_email' => 'restaurant@greengarden.al',
    'from_name'  => 'Green Garden Website',

    // --- Příjemce (kam rezervace chodí) ---
    // Může být stejná adresa jako smtp_username, nebo klidně jiná (např. rezervace@greengarden.al)
    'to_email' => 'restaurant@greengarden.al',
    'to_name'  => 'Green Garden Restaurant & Resort',
];
