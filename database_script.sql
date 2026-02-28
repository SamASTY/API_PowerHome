CREATE TABLE User
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    firstname   VARCHAR(50)  NOT NULL,
    lastname    VARCHAR(50)  NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    phoneCode   Varchar(3)   NOT NULL,
    phoneNumber varchar(15)  NOT NULL,
    password    VARCHAR(255) NOT NULL, -- hashed
    token       VARCHAR(255),
    expired_at  DATETIME
);

CREATE TABLE Habitat
(
    id      INT AUTO_INCREMENT PRIMARY KEY,
    floor   INT NOT NULL,
    area    INT NOT NULL,
    id_user INT,
    FOREIGN KEY (id_user) REFERENCES User (id) ON DELETE SET NULL
);

Create table ApplianceType
(
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE Appliance
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50) NOT NULL,
    reference  VARCHAR(50) NOT NULL UNIQUE,
    wattage    INT         NOT NULL,
    id_habitat INT,
    id_type    INT,
    FOREIGN KEY (id_type) REFERENCES ApplianceType (id),
    FOREIGN KEY (id_habitat) REFERENCES Habitat (id)
);


CREATE TABLE TimeSlot
(
    id              INT AUTO_INCREMENT PRIMARY KEY,
    begin_time      DATETIME NOT NULL,
    end_time        DATETIME NOT NULL,
    max_wattage     INT      NOT NULL CHECK (max_wattage >= 0),
    current_wattage INT DEFAULT 0 CHECK (current_wattage <= max_wattage ),
    INDEX idx_timeslot_range (begin_time, end_time)
);

CREATE TABLE Booking
(
    id_appliance INT         NOT NULL,
    id_time_slot INT         NOT NULL,
    order_ref    VARCHAR(20) NOT NULL,
    booked_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_appliance, id_time_slot),
    FOREIGN KEY (id_appliance) REFERENCES Appliance (id) ON DELETE CASCADE,
    FOREIGN KEY (id_time_slot) REFERENCES TimeSlot (id) ON DELETE CASCADE
);


CREATE PROCEDURE RecalcTimeSlotUsage(IN p_time_slot_id INT)
BEGIN
    DECLARE v_used INT;

    SELECT COALESCE(SUM(a.wattage), 0)
    INTO v_used
    FROM Booking b
             JOIN Appliance a ON a.id = b.id_appliance
    WHERE b.id_time_slot = p_time_slot_id;

    UPDATE TimeSlot
    SET current_wattage = v_used
    WHERE id = p_time_slot_id;
END;


CREATE TRIGGER trg_booking_after_insert
    AFTER INSERT
    ON Booking
    FOR EACH ROW
BEGIN
    CALL RecalcTimeSlotUsage(NEW.id_time_slot);
END;
