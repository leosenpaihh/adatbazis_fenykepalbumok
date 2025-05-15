create or replace TRIGGER ADMIN.delete_comments_compound
    FOR DELETE ON Kep
    COMPOUND TRIGGER

    -- Használjuk az SQL-szintű nested table típust
    g_ids number_ntt := number_ntt();

BEFORE EACH ROW IS
BEGIN
    g_ids.EXTEND;
    g_ids(g_ids.COUNT) := :OLD.id;
END BEFORE EACH ROW;

    AFTER STATEMENT IS
    BEGIN
        DELETE FROM hozzaszolas
        WHERE kep_id IN (SELECT COLUMN_VALUE FROM TABLE(g_ids));
    END AFTER STATEMENT;

    END;
