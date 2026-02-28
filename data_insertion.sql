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
-- 3. Appliance types (NEW)
INSERT INTO ApplianceType (name)
VALUES
    ('Iron'),
    ('Vacuum'),
    ('Washing Machine'),
    ('AirConditionner');

-- 4. Appliances (UPDATED for new schema with id_type)
-- Notes:
-- - name is kept equal to the type name (simple + consistent)
-- - reference values are unique
-- - wattage values are realistic-ish
INSERT INTO Appliance (name, reference, wattage, id_habitat, id_type)
VALUES
-- Habitat 1 (Marie)
('Washing Machine',   'WM-Samsung-WM65',     2000, 1,
 (SELECT id FROM ApplianceType WHERE name='Washing Machine')),
('Vacuum',            'VAC-Dyson-V11',        900, 1,
 (SELECT id FROM ApplianceType WHERE name='Vacuum')),
('Iron',              'IRON-Philips-GC1740', 2000, 1,
 (SELECT id FROM ApplianceType WHERE name='Iron')),

-- Habitat 2 (Pierre)
('AirConditionner',   'AC-Daikin-ARC466',    3500, 2,
 (SELECT id FROM ApplianceType WHERE name='AirConditionner')),
('Vacuum',            'VAC-Bosch-BGS05',      700, 2,
 (SELECT id FROM ApplianceType WHERE name='Vacuum')),

-- Habitat 3 (Sophie)
('Iron',              'IRON-Tefal-FV1710',   1800, 3,
 (SELECT id FROM ApplianceType WHERE name='Iron')),
('Washing Machine',   'WM-LG-F2V5',          2100, 3,
 (SELECT id FROM ApplianceType WHERE name='Washing Machine')),

-- Habitat 4 (Thomas)
('AirConditionner',   'AC-Mitsubishi-MS',    3000, 4,
 (SELECT id FROM ApplianceType WHERE name='AirConditionner'));

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
(1, 1, 'REF-20260223-001'),

(2, 3, 'REF-20260223-002'),

(3, 4, 'REF-20260224-001'),

(4, 6, 'REF-20260224-002')
;


