-- ==========================================
-- ProsDevis - Données de démonstration
-- ==========================================
USE `prosdevis`;

-- Entreprise de démo
INSERT INTO `companies` (`name`, `siret`, `rcs`, `vat_number`, `address`, `zip`, `city`, `country`, `phone`, `email`, `website`, `primary_color`, `plan`) VALUES
('Agence Web Demo', '12345678901234', 'RCS Rennes B 123 456 789', 'FR12345678901', '10 rue de la Paix', '22000', 'Saint-Brieuc', 'FR', '02 96 00 00 00', 'contact@demo.fr', 'https://demo.fr', '#2563eb', 'pro');

-- Utilisateur admin (mot de passe: Demo1234!)
-- Hash bcrypt de 'Demo1234!'
INSERT INTO `users` (`company_id`, `email`, `password`, `first_name`, `last_name`, `role`, `email_verified`, `gdpr_consent`, `gdpr_consent_at`) VALUES
(1, 'admin@demo.fr', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQyCgSMTKjUyO7VjxQXxBWUVq', 'Admin', 'Demo', 'admin', 1, 1, NOW());

-- Client de démo
INSERT INTO `clients` (`company_id`, `type`, `name`, `siret`, `contact_name`, `email`, `phone`, `address`, `zip`, `city`) VALUES
(1, 'company', 'Client Exemple SARL', '98765432109876', 'Jean Dupont', 'jean.dupont@client.fr', '06 00 00 00 01', '5 avenue Victor Hugo', '75001', 'Paris');

-- Produits catalogue
INSERT INTO `products` (`company_id`, `reference`, `name`, `description`, `unit`, `unit_price`, `vat_rate`) VALUES
(1, 'DEV-001', 'Développement web', 'Conception et développement de site web sur mesure', 'jour', 600.00, 20.00),
(1, 'INT-001', 'Intégration HTML/CSS', 'Intégration responsive multi-devices', 'heure', 75.00, 20.00),
(1, 'SEO-001', 'Audit SEO', 'Analyse complète du référencement naturel', 'forfait', 350.00, 20.00),
(1, 'MAINT-001', 'Maintenance mensuelle', 'TMA et mises à jour mensuelles', 'mois', 150.00, 20.00);

-- Séquence de numérotation
INSERT INTO `number_sequences` (`company_id`, `type`, `year`, `last_number`) VALUES
(1, 'quote', 2026, 0),
(1, 'invoice', 2026, 0);
