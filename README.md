# 🧾 ProsDevis

> Application web PHP/MySQL de création de devis **et factures** professionnels — design épuré, multi-utilisateur, PDF, conformité RGPD & préparation Factur-X.

![PHP](https://img.shields.io/badge/PHP-8.2-blue) ![MySQL](https://img.shields.io/badge/MySQL-8.0-orange) ![License](https://img.shields.io/badge/license-MIT-green)

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
- Création/édition de devis avec calcul temps réel
- Lignes drag & drop, catalogue produits, remises globales et par ligne
- Aperçu détaillé du devis avec timeline de statuts
- Envoi par email depuis l'interface
- Transformation Devis → Facture en 1 clic
- Génération PDF Dompdf (inline ou téléchargement)

### 🧾 Gestion des Factures
- Numérotation séquentielle non modifiable (ex: FAC-2026-0001)
- Génération depuis un devis accepté
- Liste des factures avec filtres, solde dû et échéances en retard
- Fiche facture avec aperçu, suivi d'encaissement et progression de paiement
- Paiement total ou partiel avec recalcul automatique du solde
- Envoi de relances email sur factures en retard

### 💼 Gestion des Clients & Entreprises
- Annuaire clients avec historique
- Gestion multi-entreprise (logo, couleurs, SIRET, RCS)
- Templating par entreprise

### 💰 Fiscalité
- Gestion TVA multi-pays (FR 20%, DE 19%, etc.)
- Remises globales et par ligne
- Acomptes et conditions de paiement
- Échéances et suivi du solde restant dû

### ✍️ Signature Électronique
- Solution intégrée (checkbox + email de confirmation)
- Compatible HelloSign / DocuSign API

### 📊 Dashboard
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
├── public/               # Racine web publique
│   ├── index.php         # Landing page
│   ├── login.php         # Page de connexion
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
├── app/
│   ├── Controllers/
│   │   ├── QuoteController.php
│   │   ├── QuotePdfController.php
│   │   └── InvoiceController.php
│   ├── Models/
│   │   ├── Quote.php
│   │   └── Invoice.php
│   ├── Views/
│   │   ├── quotes/
│   │   └── invoices/
│   ├── Middleware/
│   ├── Helpers/
│   ├── routes_quotes.php
│   └── routes_invoices.php
├── config/
│   ├── database.php
│   ├── app.php
│   └── mail.php
├── database/
│   ├── schema.sql        # Structure complète de la BDD
│   └── seeds/            # Données de démonstration
├── templates/
│   └── devis/            # Templates PDF historiques / custom
├── storage/
│   ├── pdfs/
│   ├── logos/
│   └── logs/
├── vendor/               # Dépendances Composer
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

# 2. Installer les dépendances
composer install
bash composer_require_dompdf.sh

# 3. Configurer l'environnement
cp .env.example .env
# Éditer .env avec vos paramètres

# 4. Créer la base de données
mysql -u root -p < database/schema.sql

# 5. (Optionnel) Charger les données de démo
mysql -u root -p prosdevis < database/seeds/demo.sql
```

---

## 🛡️ Sécurité

- Tous les mots de passe hashés avec `password_hash()` (bcrypt)
- Tokens CSRF sur chaque formulaire
- Requêtes préparées PDO uniquement
- Rate limiting sur le login
- Headers HTTP sécurisés via `.htaccess`
- Génération PDF sans ressources distantes activées

---

## 📋 Roadmap

- [x] Structure du projet
- [x] Schéma de base de données
- [x] Système d'authentification
- [x] Landing page
- [x] Module devis (CRUD principal + PDF)
- [x] Conversion devis → facture
- [x] Module factures (liste, détail, paiement, relance)
- [ ] Dashboard
- [ ] Signature électronique
- [ ] Blog & SEO
- [ ] Format Factur-X
- [ ] PDF facture
- [ ] Rappels automatiques planifiés

---

## 📄 License

MIT © [Swerkx / Sazar](https://github.com/Sazar)
