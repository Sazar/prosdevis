# 🧾 ProsDevis

> Application web PHP/MySQL de création de devis professionnels — design épuré, multi-utilisateur, PDF, signature électronique, conformité RGPD & Factur-X.

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
- Création de devis avec drag & drop des lignes
- Autosave toutes les 30 secondes
- Templates personnalisables (logo, couleurs, mentions)
- Transformation Devis → Facture en 1 clic
- Génération PDF conforme (SIRET, TVA intracom, mentions légales)
- Préparation format Factur-X (obligation France 2026)

### 💼 Gestion des Clients & Entreprises
- Annuaire clients avec historique
- Gestion multi-entreprise (logo, couleurs, SIRET, RCS)
- Templating par entreprise

### 💰 Fiscalité
- Gestion TVA multi-pays (FR 20%, DE 19%, etc.)
- Remises globales et par ligne
- Acomptes et conditions de paiement

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
│   ├── Models/
│   ├── Views/
│   ├── Middleware/
│   └── Helpers/
├── config/
│   ├── database.php
│   ├── app.php
│   └── mail.php
├── database/
│   ├── schema.sql        # Structure complète de la BDD
│   └── seeds/            # Données de démonstration
├── templates/
│   └── devis/            # Templates PDF
├── storage/
│   ├── pdfs/
│   ├── logos/
│   └── logs/
├── vendor/               # Dépendances Composer
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

---

## 📋 Roadmap

- [x] Structure du projet
- [x] Schéma de base de données
- [x] Système d'authentification
- [x] Landing page
- [ ] Dashboard
- [ ] Création de devis
- [ ] Génération PDF
- [ ] Signature électronique
- [ ] Blog & SEO
- [ ] Format Factur-X

---

## 📄 License

MIT © [Swerkx / Sazar](https://github.com/Sazar)
