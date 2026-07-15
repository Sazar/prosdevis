# 🧾 ProsDevis

> Application web PHP/MySQL de création de devis **et factures** professionnels — design épuré, multi-utilisateur, PDF Dompdf, dashboard analytique, signature électronique client, Factur-X, blog SEO, **export comptable CSV/FEC** et **SMTP/provider email**.

![PHP](https://img.shields.io/badge/PHP-8.2-blue) ![MySQL](https://img.shields.io/badge/MySQL-8.0-orange) ![Dompdf](https://img.shields.io/badge/Dompdf-3.x-teal) ![Chart.js](https://img.shields.io/badge/Chart.js-4.4-orange) ![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Fonctionnalités

### 📤 Export Comptable
- **CSV** — tableau complet de toutes les factures sur période choisie, BOM UTF-8 pour Excel
- **FEC NF Z55-200** — Fichier des Écritures Comptables conforme DGFiP (18 colonnes, délimiteur pipe, nommage `SIRETFECannée.txt`)
- Sélecteur de période depuis l'interface back-office
- Traçabilité dans `activity_logs`

### 📧 Mailer multi-driver
- **SMTP natif** — connexion socket PHP, STARTTLS, AUTH LOGIN, multipart HTML+texte
- **Brevo (ex-Sendinblue)** — API REST v3
- **Mailgun** — API REST, régions EU/US
- **Fallback `mail()`** — sans configuration
- Driver sélectionnable via `.env` (`MAIL_DRIVER=smtp|brevo|mailgun|mail`)

### 🌐 Blog & SEO
- Blog public indexable avec listing et pages article dédiées
- Administration back-office des articles : brouillon / publication
- Champs SEO : `meta_title`, `meta_desc`, `og_image`, `canonical`
- Génération de `sitemap.xml`
- Open Graph / Twitter card pour partage social

### 🔐 Authentification & Sécurité
- Système de login avancé (JWT + sessions sécurisées)
- Rôles : Admin, Collaborateur, Comptable
- 2FA optionnel (TOTP)
- Headers sécurisés (CSP, HSTS, X-Frame-Options)
- Protection CSRF, XSS, injection SQL
- Conformité RGPD (consentement, droit à l'oubli, export)

### ✍️ Signature Électronique
- Génération d'un lien public de signature par devis
- Signature manuscrite sur canvas HTML5
- Acceptation/refus avec preuve stockée (horodatage, IP, user-agent)

### 📊 Dashboard Analytique
- 6 KPIs temps réel : CA facturé, CA encaissé, solde dû, devis en attente, factures en retard, taux de conversion
- Graphique CA mensuel 6 mois (Chart.js), donut statuts, fil d'activité, top clients

### 📄 Gestion des Devis
- Numérotation séquentielle (DEV-2026-0001), calcul temps réel, drag & drop, catalogue produits
- PDF Dompdf, transformation Devis → Facture en 1 clic

### 🧾 Gestion des Factures
- Numérotation séquentielle (FAC-2026-0001), paiement partiel/total
- Relances auto (cron, 3 niveaux d'escalade), XML Factur-X/EN16931, PDF Dompdf

---

## 🗂️ Structure du Projet

```
prosdevis/
├── public/
├── app/
│   ├── Controllers/
│   │   ├── BlogController.php
│   │   ├── DashboardController.php
│   │   ├── ExportController.php
│   │   ├── FacturXController.php
│   │   ├── InvoiceController.php
│   │   ├── InvoicePdfController.php
│   │   ├── QuoteController.php
│   │   ├── QuotePdfController.php
│   │   ├── ReminderController.php
│   │   └── SignatureController.php
│   ├── Services/
│   │   ├── AccountingExport.php
│   │   ├── FacturX.php
│   │   ├── Mailer.php
│   │   └── ReminderMailer.php
│   ├── Views/
│   │   ├── blog/
│   │   ├── dashboard/
│   │   ├── exports/
│   │   ├── invoices/
│   │   ├── quotes/
│   │   ├── reminders/
│   │   ├── signatures/
│   │   └── layouts/
│   ├── routes_blog.php
│   ├── routes_dashboard.php
│   ├── routes_exports.php
│   ├── routes_facturx.php
│   ├── routes_invoices.php
│   ├── routes_quotes.php
│   ├── routes_reminders.php
│   └── routes_signatures.php
├── bin/
│   └── send_reminders.php
├── database/
│   ├── schema.sql
│   └── migrations/
├── storage/
├── tests/
├── .env.example
└── README.md
```

---

## 🚀 Installation

```bash
git clone https://github.com/Sazar/prosdevis.git
cd prosdevis
composer install
cp .env.example .env   # éditer les valeurs
mysql -u root -p < database/schema.sql
mysql -u root -p prosdevis < database/migrations/004_invoice_reminders.sql
```

### Cron relances
```bash
0 8 * * 1-5 php /var/www/prosdevis/bin/send_reminders.php >> /var/log/prosdevis_reminders.log 2>&1
```

---

## 🛡️ Sécurité

- Mots de passe hashés `bcrypt`, tokens CSRF, requêtes PDO préparées
- Rate limiting login, headers HTTP sécurisés via `.htaccess`
- Génération PDF sans ressources distantes
- Signature électronique tracée : horodatage, IP, user-agent, payload JSON
- Secrets dans `.env` (jamais en dur dans le code)

---

## 📋 Roadmap — ✅ Complet

- [x] Structure du projet & schéma de base de données
- [x] Authentification (login, sessions, CSRF, rôles, 2FA)
- [x] Landing page publique
- [x] Module **Devis** — CRUD, calcul temps réel, drag & drop, catalogue
- [x] PDF Devis — Dompdf, template A4
- [x] Module **Factures** — liste, fiche, paiement partiel/total
- [x] PDF Factures — template A4, mentions légales art. L.441-10
- [x] **Dashboard analytique** — KPIs, CA Chart.js, activité, top clients
- [x] **Signature électronique** — canvas HTML5, preuve stockée
- [x] **Relances automatiques** — cron, 3 niveaux, historique
- [x] **Factur-X / EN 16931** — XML téléchargeable
- [x] **Blog & SEO** — public, admin, Open Graph, sitemap
- [x] **Export comptable** — CSV Excel + FEC NF Z55-200
- [x] **Mailer multi-driver** — SMTP, Brevo, Mailgun, fallback

---

## 📄 License

MIT © [Swerkx / Sazar](https://github.com/Sazar)
