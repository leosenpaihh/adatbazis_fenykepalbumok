-- Trigger az albumnev duplikacio megakadalyozasara
CREATE OR REPLACE TRIGGER prevent_duplicate_album
    BEFORE INSERT ON FENYKEPALBUM
    FOR EACH ROW
DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*)
    INTO v_count
    FROM FENYKEPALBUM
    WHERE LOWER(NEV) = LOWER(:NEW.NEV)
      AND FELHASZNALO_FELHASZNALONEV = :NEW.FELHASZNALO_FELHASZNALONEV;

    IF v_count > 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'Már van ilyen nevű albumod!');
    END IF;
END;
/