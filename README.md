# 🧾 ProsDevis

> Application web PHP/MySQL de création de devis **et factures** professionnels — design épuré, multi-utilisateur, PDF Dompdf, dashboard analytique, **signature électronique client**, conformité RGPD & préparation Factur-X.

![PHP](https://img.shields.io/badge/PHP-8.2-blue) ![MySQL](https://img.shields.io/badge/MySQL-8.0-orange) ![Dompdf](https://img.shields.io/badge/Dompdf-3.x-teal) ![Chart.js](https://img.shields.io/badge/Chart.js-4.4-orange) ![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Fonctionnalités

### 🔐 Authentification & Sécurité
- Système de login avancé (JWT + sessions sécurisées)
- Rôles : Admin, Collaborateur, Comptable
- 2FA optionnel (TOTP)
- Headers sécurisés (CSP, HSTS, X-Frame-Options)
- Protection CSRF, XSS, injection SQL
- Conformité RGPD (consentement, droit à l'oubli, export)

### ✍️ Signature Électronique
- Génération d’un lien public de signature par devis
- Envoi email du lien de validation client
- Signature manuscrite sur canvas HTML5
- Consentement explicite avant validation
- Acceptation automatique du devis après signature
- Refus possible avec motif, journalisé dans `activity_logs`
- Horodatage, IP et user-agent conservés dans la preuve de signature

### 📊 Dashboard Analytique
- 6 KPIs temps réel : CA facturé, CA encaissé, solde dû, devis en attente, factures en retard, taux de conversion
- Graphique CA mensuel 6 mois (barres + courbe, Chart.js)
- Donut répartition des statuts devis
- Fil d'activité récente (12 derniers événements)
- Top 5 clients par CA avec barres de progression
- Responsive mobile, dark mode natif

### 📄 Gestion des Devis
- Numérotation séquentielle non modifiable (ex: DEV-2026-0001)
- Création/édition avec calcul temps réel, drag & drop, catalogue produits
- Remises globales et par ligne, acomptes, conditions de paiement
- Aperçu détaillé, envoi par email, timeline de statuts
- Transformation Devis → Facture en 1 clic
- **Génération PDF** (Dompdf) : inline navigateur ou téléchargement

### 🧾 Gestion des Factures
- Numérotation séquentielle non modifiable (ex: FAC-2026-0001)
- Génération depuis un devis accepté ou création directe
- Liste avec filtres, solde dû, échéances en retard
- Fiche facture : aperçu HTML, barre de progression d'encaissement
- Paiement total ou partiel avec recalcul automatique du solde
- Relances email sur factures en retard
- **Génération PDF** (Dompdf) : template A4, suivi paiement, mentions légales art. L.441-10

### 💼 Gestion des Clients & Entreprises
- Annuaire clients avec historique
- Gestion multi-entreprise (logo, couleurs, SIRET, RCS)
- Templating par entreprise

### 💰 Fiscalité
- Gestion TVA multi-pays (FR 20%, DE 19%, etc.)
- Remises globales et par ligne
- Acomptes et conditions de paiement
- Échéances et suivi du solde restant dû

### 🌐 Landing & Marketing
- Landing page publique avec pricing
- Blog intégré (SEO)
- Open Graph pour partage réseau social
- Responsive mobile (artisans sur chantier)

---

## 🗂️ Structure du Projet

```
prosdevis/
├── public/
├── app/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── QuoteController.php
│   │   ├── QuotePdfController.php
│   │   ├── InvoiceController.php
│   │   ├── InvoicePdfController.php
│   │   └── SignatureController.php
│   ├── Models/
│   │   ├── Dashboard.php
│   │   ├── Quote.php
│   │   ├── Invoice.php
│   │   └── Signature.php
│   ├── Views/
│   │   ├── dashboard/
│   │   ├── quotes/
│   │   ├── invoices/
│   │   ├── signatures/
│   │   └── layouts/
│   │       ├── app.php
│   │       └── public.php
│   ├── routes_dashboard.php
│   ├── routes_quotes.php
│   ├── routes_invoices.php
│   └── routes_signatures.php
├── database/
├── storage/
├── vendor/
└── README.md
```

---

## 🚀 Installation

### Prérequis
- PHP 8.2+
- MySQL 8.0+
- Composer
- Serveur Apache/Nginx avec mod_rewrite
- Fonction `mail()` configurée ou SMTP relay côté serveur

### Étapes

```bash
git clone https://github.com/Sazar/prosdevis.git
cd prosdevis
composer install
bash composer_require_dompdf.sh
cp .env.example .env
mysql -u root -p < database/schema.sql
```

---

## 🛡️ Sécurité

- Mots de passe hashés avec `password_hash()` (bcrypt)
- Tokens CSRF sur chaque formulaire
- Requêtes préparées PDO uniquement
- Rate limiting sur le login
- Headers HTTP sécurisés via `.htaccess`
- Génération PDF sans ressources distantes (`isRemoteEnabled: false`)
- Signature électronique tracée : horodatage, IP, user-agent, payload JSON

---

## 📋 Roadmap

### ✅ Livré
- [x] Structure du projet & schéma de base de données
- [x] Système d'authentification (login, sessions, CSRF, rôles)
- [x] Landing page publique
- [x] Module **Devis** — CRUD complet, calcul temps réel, drag & drop, catalogue produits
- [x] Aperçu devis (statuts, timeline, envoi email, conversion en facture)
- [x] **PDF Devis** — Dompdf, template A4, inline + téléchargement
- [x] Module **Factures** — liste, fiche détail, paiement partiel/total, relance email
- [x] **PDF Factures** — template A4, suivi encaissement, mentions légales art. L.441-10
- [x] **Dashboard analytique** — 6 KPIs, CA mensuel Chart.js, donut devis, activité, top clients
- [x] **Signature électronique** — lien public, canvas HTML5, acceptation/refus, preuve stockée

### 🔜 À venir
- [ ] **Rappels automatiques planifiés** — cron job, relances progressives
- [ ] **Format Factur-X** — obligation France 2026, XML embarqué dans le PDF
- [ ] **Blog & SEO** — contenu marketing, Open Graph
- [ ] **Export comptable** — CSV, FEC, synchronisation outils tiers
- [ ] **SMTP / Email provider** — fiabilisation envoi emails (Mailgun, Postmark, Brevo)

---

## 📄 License

MIT © [Swerkx / Sazar](https://github.com/Sazar)
