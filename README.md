# 🧾 ProsDevis

> Application web PHP/MySQL de création de devis **et factures** professionnels — design épuré, multi-utilisateur, PDF Dompdf, conformité RGPD & préparation Factur-X.

![PHP](https://img.shields.io/badge/PHP-8.2-blue) ![MySQL](https://img.shields.io/badge/MySQL-8.0-orange) ![Dompdf](https://img.shields.io/badge/Dompdf-3.x-teal) ![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Fonctionnalités

### 🔐 Authentification & Sécurité
- Système de login avancé (JWT + sessions sécurisées)
- Rôles : Admin, Collaborateur, Comptable
- 2FA optionnel (TOTP)
- Headers sécurisés (CSP, HSTS, X-Frame-Options)
- Protection CSRF, XSS, injection SQL
- Conformité RGPD (consentement, droit à l'oubli, export)

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
- Liste des factures : filtres statut/recherche, solde dû, échéances en retard
- Fiche facture : aperçu HTML, barre de progression d'encaissement
- Paiement total ou partiel avec recalcul automatique du solde
- Relances email sur factures en retard
- **Génération PDF** (Dompdf) : template dédié avec suivi paiement, mentions légales (art. L.441-10)

### 💼 Gestion des Clients & Entreprises
- Annuaire clients avec historique
- Gestion multi-entreprise (logo, couleurs, SIRET, RCS)
- Templating par entreprise

### 💰 Fiscalité
- Gestion TVA multi-pays (FR 20%, DE 19%, etc.)
- Remises globales et par ligne
- Acomptes et conditions de paiement
- Échéances et suivi du solde restant dû

### ✍️ Signature Électronique *(à venir)*
- Solution intégrée (checkbox + email de confirmation)
- Compatible HelloSign / DocuSign API

### 📊 Dashboard *(à venir)*
- CA en cours, devis en attente, taux de conversion
- Graphiques et statistiques
- Dark mode

### 🌐 Landing & Marketing
- Landing page publique avec pricing
- Blog intégré (SEO)
- Open Graph pour partage réseau social
- Responsive mobile (artisans sur chantier)

---

## 🗂️ Structure du Projet

```
prosdevis/
├── public/                  # Racine web publique
│   ├── index.php            # Landing page
│   ├── login.php            # Page de connexion
│   └── assets/
│       ├── css/
│       ├── js/
│       └── img/
├── app/
│   ├── Controllers/
│   │   ├── QuoteController.php
│   │   ├── QuotePdfController.php
│   │   ├── InvoiceController.php
│   │   └── InvoicePdfController.php
│   ├── Models/
│   │   ├── Quote.php
│   │   └── Invoice.php
│   ├── Views/
│   │   ├── quotes/
│   │   │   ├── index.php
│   │   │   ├── form.php
│   │   │   ├── show.php
│   │   │   └── pdf_template.php
│   │   └── invoices/
│   │       ├── index.php
│   │       ├── show.php
│   │       └── pdf_template.php
│   ├── Middleware/
│   ├── Helpers/
│   ├── routes_quotes.php
│   └── routes_invoices.php
├── config/
│   ├── database.php
│   ├── app.php
│   └── mail.php
├── database/
│   ├── schema.sql           # Structure complète de la BDD
│   └── seeds/               # Données de démonstration
├── storage/
│   ├── pdfs/
│   ├── logos/
│   └── logs/
├── vendor/                  # Dépendances Composer
├── composer_require_dompdf.sh
├── .env.example
├── .htaccess
├── composer.json
└── README.md
```

---

## 🚀 Installation

### Prérequis
- PHP 8.2+
- MySQL 8.0+
- Composer
- Serveur Apache/Nginx avec mod_rewrite

### Étapes

```bash
# 1. Cloner le repo
git clone https://github.com/Sazar/prosdevis.git
cd prosdevis

# 2. Installer les dépendances (dont Dompdf)
composer install
bash composer_require_dompdf.sh

# 3. Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos paramètres DB, mail, etc.

# 4. Créer la base de données
mysql -u root -p < database/schema.sql

# 5. (Optionnel) Charger les données de démo
mysql -u root -p prosdevis < database/seeds/demo.sql
```

---

## 🛡️ Sécurité

- Mots de passe hashés avec `password_hash()` (bcrypt)
- Tokens CSRF sur chaque formulaire
- Requêtes préparées PDO uniquement
- Rate limiting sur le login
- Headers HTTP sécurisés via `.htaccess`
- Génération PDF sans ressources distantes activées (Dompdf `isRemoteEnabled: false`)

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
- [x] **PDF Factures** — template A4 dédié, suivi encaissement, mentions légales art. L.441-10

### 🔜 À venir
- [ ] **Dashboard** — CA, taux de conversion, graphiques, KPIs
- [ ] **Signature électronique** — intégrée + HelloSign/DocuSign
- [ ] **Rappels automatiques planifiés** — cron job, relances progressives
- [ ] **Format Factur-X** — obligation France 2026, XML embarqué dans le PDF
- [ ] **Blog & SEO** — contenu marketing, Open Graph
- [ ] **Export comptable** — CSV, FEC, synchronisation avec des outils tiers

---

## 📄 License

MIT © [Swerkx / Sazar](https://github.com/Sazar)
