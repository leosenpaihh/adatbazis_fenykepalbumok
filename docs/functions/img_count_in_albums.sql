-- Fuggveny a kepek szamanak meghatarozasara egy albumban
CREATE OR REPLACE FUNCTION img_count_in_albums(p_album_id IN NUMBER)
    RETURN NUMBER
    IS
    v_count NUMBER := 0;
BEGIN
    SELECT COUNT(*)
    INTO v_count
    FROM KEPFENYKEPALBUM
    WHERE FENYKEPALBUM_ID = p_album_id;

    RETURN v_count;
EXCEPTION
    WHEN OTHERS THEN
        RETURN -1;
END;
/