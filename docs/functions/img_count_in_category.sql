CREATE OR REPLACE FUNCTION img_count_in_category(p_category_name IN VARCHAR2)
    RETURN NUMBER IS v_count NUMBER := 0;
BEGIN
    SELECT COUNT(*) INTO v_count FROM KEPKATEGORIA WHERE KATEGORIA_NEV = p_category_name;

    RETURN v_count;
EXCEPTION
    WHEN OTHERS THEN
        RETURN -1;
END;
/