-- ========================================
-- INSERTS - Données réalistes pour tests
-- ========================================

-- 1. Utilisateurs (propriétaires d'habitats)
INSERT INTO User (firstname, lastname, email, password, phoneCode, phoneNumber)
VALUES ('Marie', 'Dupont', 'marie.dupont@email.com', '$2y$10$t0qImsuc9NiEi.R1C3AIle/5poZPnuDnl2JMHOoqc/qzIFSMqoOWC',
        '+33', '1234567890'), -- AZERTY123qs
       ('Pierre', 'Martin', 'pierre.martin@email.com', '$2y$10$AZRzkJR4fod3FJwEgdHm/e81bv6x1aUF2uzHWqlMWuXcpsmCF6Z96',
        '+33', '1234567890'), -- CQFDenft1234
       ('Sophie', 'Leroy', 'sophie.leroy@email.com', '$2y$10$YRVKk8BuL0Zu62vSeaLOz.975OvRvfPlJbbRulW9stRgfn9Ad1j8W',
        '+33', '1234567890'), -- MOTdePASSE123
       ('Thomas', 'Moreau', 'thomas.moreau@email.com', '$2y$10$dYde79bs1soh38DClbOBYeUuhzS1tuBN/4LQSZ4m/MYrfgO/2CgJm',
        '+33', '1234567890'), -- LNeesrb5856S4s248sd59SnuJ6sfv5B2GGerb
       ('Jean-Luc', 'Testard', 'jean-luc.testard@email.com',
        '$2y$10$9R593nycKIbxsEUJJ5Mc1.KmVsPFTe8l796h5NCnv/HAfMycwZr2C', '+33', '1234567890');
-- TestPass123! ✓-- Vérification: SELECT * FROM User;

-- 2. Habitats (appartements/maisons)
INSERT INTO Habitat (floor, area, id_user)
VALUES (2, 65, 1),  -- Marie Dupont, 2ème étage, 65m²
       (0, 120, 2), -- Pierre Martin, RDC, 120m² (maison)
       (5, 45, 3),  -- Sophie Leroy, 5ème étage, 45m²
       (1, 85, 4);
-- Thomas Moreau, 1er étage, 85m²

-- 3. Appareils électroménagers
INSERT INTO Appliance (name, reference, wattage, id_habitat)
VALUES
-- Appareils de Marie (id_habitat=1)
('Lave-linge', 'LL-Samsung-WM65', 2000, 1),
('Réfrigérateur', 'REF-Bosch-KGN36', 150, 1),
('Micro-ondes', 'MO-Sharp-R280', 900, 1),

-- Appareils de Pierre (id_habitat=2)
('Climatiseur', 'CLIM-Daikin-ARC466', 3500, 2),
('Sèche-linge', 'SL-Bosch-WTH852', 2600, 2),
('Aspirateur', 'ASP-Dyson-V11', 900, 2),

-- Appareils de Sophie (id_habitat=3)
('Four électrique', 'FOUR-Electrolux-EOS', 3200, 3),
('Plaques induction', 'PLAQUES-Siemens-EX875', 7200, 3),

-- Appareils de Thomas (id_habitat=4)
('Chauffe-eau', 'CE-Ariston-80L', 2000, 4),
('Congélateur', 'CONG-Liebherr-GP1213', 120, 4);

-- 4. Créneaux horaires disponibles (disponibles pour réservation)
INSERT INTO TimeSlot (begin_time, end_time, max_wattage)
VALUES
-- Lundi matin (basse consommation)
('2026-02-23 08:00:00', '2026-02-23 12:00:00', 7200),
('2026-02-23 14:00:00', '2026-02-23 18:00:00', 7200),

-- Lundi soir (heures creuses)
('2026-02-23 20:00:00', '2026-02-23 23:00:00', 10000),

-- Mardi matin
('2026-02-24 09:00:00', '2026-02-24 13:00:00', 7200),
('2026-02-24 15:00:00', '2026-02-24 19:00:00', 7200),

-- Mardi soir
('2026-02-24 21:00:00', '2026-02-24 23:59:00', 10000);

INSERT INTO Booking (id_appliance, id_time_slot, order_ref)
VALUES
-- Marie réserve lave-linge lundi matin
(1, 1, 'REF-20260223-001'),

-- Pierre réserve climatiseur lundi soir (heures creuses)
(4, 3, 'REF-20260223-002'),

-- Sophie réserve plaques mardi matin
(8, 4, 'REF-20260224-001'),

-- Thomas réserve chauffe-eau mardi soir
(10, 6, 'REF-20260224-002')
;


