CREATE OR REPLACE FUNCTION img_count_at_location(p_location_id IN NUMBER)
    RETURN NUMBER IS v_count NUMBER := 0;
BEGIN
    SELECT COUNT(*) INTO v_count FROM KEP WHERE TELEPULES_ID = p_location_id;

    RETURN v_count;
EXCEPTION
    WHEN OTHERS THEN
        RETURN -1;
END;
/