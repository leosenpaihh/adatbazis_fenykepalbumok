--nested table típus egy számokat ami egy számokat tartalmazó tomb
--tartalmazza a törölt sorok (id)-jét, és ezzel együtt működik a delete_comments_compound trigger
CREATE OR REPLACE TYPE number_ntt AS TABLE OF NUMBER;

