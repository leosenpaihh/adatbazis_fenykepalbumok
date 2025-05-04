-- Trigger a törölt képhez tartozó hozzászólások törlésére
CREATE OR REPLACE TRIGGER delete_comments
    AFTER DELETE ON KEP
    FOR EACH ROW
BEGIN
    DELETE FROM HOZZASZOLAS
    WHERE kep_id = :OLD.id;
END;
/