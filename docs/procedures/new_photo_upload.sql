CREATE OR REPLACE PROCEDURE new_photo_upload(
    p_cim IN VARCHAR2,
    p_leiras IN CLOB,
    p_binaris IN BLOB,
    p_felhasznalo IN VARCHAR2,
    p_telepules_id IN NUMBER,
    p_uj_id OUT NUMBER
) IS
BEGIN
    INSERT INTO KEP (ID,
                     FELTOLTESI_DATUM,
                     CIM,
                     LEIRAS,
                     FELHASZNALO_FELHASZNALONEV,
                     TELEPULES_ID,
                     KEP_BINARIS)
    VALUES (KEP_SEQ.NEXTVAL,
            CURRENT_TIMESTAMP,
            p_cim,
            p_leiras,
            p_felhasznalo,
            p_telepules_id,
            p_binaris)
    RETURNING ID INTO p_uj_id;
END;
/