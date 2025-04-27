<?php
require_once '../includes/base.php';
session_start();
include __DIR__ . '/shared/menu.php';
include('../includes/db.php');

// OCILob objektum stringgé alakítása
function lobToString($value) {
    return ($value instanceof OCILob) ? $value->load() : $value;
}

// Kép adatainak lekérdezése
if (!isset($_GET['kep_id'])) {
    die("Hiányzik a kép azonosító (kep_id)!");
}
$kepId = intval($_GET['kep_id']);

$query = "SELECT 
    k.ID, 
    TO_CHAR(k.FELTOLTESI_DATUM, 'YYYY-MM-DD HH24:MI:SS') AS feltoltesi_datum, 
    k.CIM, 
    k.KEP_BINARIS, 
    dbms_lob.getlength(k.KEP_BINARIS) AS blob_length, 
    k.LEIRAS, 
    k.FELHASZNALO_FELHASZNALONEV, 
    t.TELEPULES AS telepules_nev,
    NVL(cat.KATEGORIANK, 'nincs kategória hozzárendelve') AS KATEGORIANK
FROM Kep k
LEFT JOIN Telepules t ON k.TELEPULES_ID = t.ID
LEFT JOIN (
    SELECT kep_id, LISTAGG(kategoria_nev, ', ') WITHIN GROUP (ORDER BY kategoria_nev) AS KATEGORIANK
    FROM KepKategoria
    GROUP BY kep_id
) cat ON k.ID = cat.kep_id
WHERE k.ID = :kep_id";
$stid = oci_parse($conn, $query);
oci_bind_by_name($stid, ':kep_id', $kepId);
oci_execute($stid);
$img = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_LOBS);

if (!$img) {
    die("Nincs ilyen kép.");
}
$img = array_map('lobToString', $img);

$localImageTime = '';
if (!empty($img['FELTOLTESI_DATUM'])) {
    $dt = DateTime::createFromFormat("Y-m-d H:i:s", $img['FELTOLTESI_DATUM'], new DateTimeZone('UTC'));
    if ($dt) {
        $dt->setTimezone(new DateTimeZone('Europe/Budapest'));
        $localImageTime = $dt->format('Y.m.d');
    }
}

// Hozzászólások lekérdezése (szülőkkel együtt)
$commentQuery = "SELECT 
    h.id, 
    h.szoveg, 
    TO_CHAR(h.datum, 'YYYY-MM-DD HH24:MI:SS') AS datum, 
    h.felhasznalo_felhasznalonev,
    h.szulo_id
FROM Hozzaszolas h
WHERE h.kep_id = :kep_id
ORDER BY h.datum DESC"; // Újabb hozzászólások előre

$stidComments = oci_parse($conn, $commentQuery);
oci_bind_by_name($stidComments, ':kep_id', $kepId);
oci_execute($stidComments);

// Hozzászólások beolvasása és szülő-gyerek kapcsolatok kialakítása
$comments = [];
while ($row = oci_fetch_assoc($stidComments)) {
    $row = array_change_key_case($row, CASE_LOWER);
    $row['szoveg'] = lobToString($row['szoveg']);
    $row['replies'] = [];
    $comments[$row['id']] = $row;
}

$commentTree = [];
foreach ($comments as $id => &$comment) {
    if ($comment['szulo_id'] === null) {
        $commentTree[] = &$comment;
    } else {
        $comments[$comment['szulo_id']]['replies'][] = &$comment;
    }
}
unset($comment);

$prErtekelesek = null;
if (isset($_SESSION['felhasznalo'])) {
    $ertekelesek = [];
    $queryErtekeles = "SELECT * FROM ERTEKELES WHERE FELHASZNALO_FELHASZNALONEV = :felhasznalonev AND KEP_ID = :kep";

    $stidErtekeles = oci_parse($conn, $queryErtekeles);
    oci_bind_by_name($stidErtekeles, ':felhasznalonev', $_SESSION['felhasznalo']['felhasznalonev']);
    oci_bind_by_name($stidErtekeles, ':kep', $_GET['kep_id']);
    oci_execute($stidErtekeles);

    $ertekelesek = oci_fetch_assoc($stidErtekeles);

    if ($ertekelesek) {
        $prErtekelesek = array(
            $ertekelesek["KEP_ID"] => $ertekelesek["PONTSZAM"]
        );
    } else {
        $prErtekelesek = null;
    }
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($img['CIM']) ?> - Kép megtekintése</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=delete,reply" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/styles/favicon.ico" type="image/ico">
    <base href="<?= BASE_URL ?>">
    <script>
        function toggleReplyBox(commentId) {
            var replyForm = document.getElementById('reply-form-' + commentId);
            replyForm.style.display = replyForm.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</head>
<body>
<div class="page-container">
    <div class="wrapper">
        <div class="photo-review-container">
            <!-- Kép -->
            <div class="image-container">
                <?php if (!empty($img['KEP_BINARIS'])): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($img['KEP_BINARIS']) ?>" alt="<?= htmlspecialchars($img['CIM']) ?>" class="photo-image">
                <?php else: ?>
                    <p>Nincs kép elérhető.</p>
                <?php endif; ?>
            </div>

            <!-- Kép adatok -->
            <div class="image-info">
                <h2><?= htmlspecialchars($img['CIM']) ?></h2>
                <p><strong>Feltöltötte:</strong> <?= htmlspecialchars($img['FELHASZNALO_FELHASZNALONEV']) ?></p>
                <p><strong>Feltöltési dátum:</strong> <?= htmlspecialchars($localImageTime) ?></p>
                <p><strong>Település:</strong> <?= htmlspecialchars($img['telepules_nev'] ?? 'Nincs település hozzárendelve') ?></p>
                <p><strong>Kategóriák:</strong> <?= htmlspecialchars($img['KATEGORIANK']) ?></p>
                <p><strong>Leírás:</strong> <?= nl2br(htmlspecialchars($img['LEIRAS'])) ?></p>

        <?php if (isset($_SESSION['felhasznalo'])): ?>
        <form method="POST" action="controllers/rating_handler.php?kep_id=<?php echo $img['ID']; ?>">
            <div class="rating">
                <?php if (!isset($prErtekelesek[$img['ID']])): ?>
                <button name="letrehozas" type="submit" class="rating-button" title="Értékelés hozzáadása">
                    <i class="fa fa-check"></i>
                </button>
                <?php else: ?>
                <button name="modositas" type="submit" class="rating-button" title="Értékelés módosítása">
                    <i class="fa fa-pencil"></i>
                </button>
                <button name="torles" type="submit" class="rating-button" title="Értékelés törlése">
                    <i class="fa fa-x"></i>
                </button>
                <?php endif; ?>
                <input type="radio" id="star5" name="rating" value="5" <?php echo (isset($prErtekelesek[$img['ID']]) && $prErtekelesek[$img['ID']] == 5) ? 'checked' : ''; ?> /><label for="star5" title="5"><i class="fa-solid fa-star"></i></label>
                <input type="radio" id="star4" name="rating" value="4" <?php echo (isset($prErtekelesek[$img['ID']]) && $prErtekelesek[$img['ID']] == 4) ? 'checked' : ''; ?> /><label for="star4" title="4"><i class="fa-solid fa-star"></i></label>
                <input type="radio" id="star3" name="rating" value="3" <?php echo (isset($prErtekelesek[$img['ID']]) && $prErtekelesek[$img['ID']] == 3) ? 'checked' : ''; ?> /><label for="star3" title="3"><i class="fa-solid fa-star"></i></label>
                <input type="radio" id="star2" name="rating" value="2" <?php echo (isset($prErtekelesek[$img['ID']]) && $prErtekelesek[$img['ID']] == 2) ? 'checked' : ''; ?> /><label for="star2" title="2"><i class="fa-solid fa-star"></i></label>
                <input type="radio" id="star1" name="rating" value="1" <?php echo (isset($prErtekelesek[$img['ID']]) && $prErtekelesek[$img['ID']] == 1) ? 'checked' : ''; ?> /><label for="star1" title="1"><i class="fa-solid fa-star"></i></label>
            </div>
        </form>
        <?php endif ; ?>

        <?php if (isset($_SESSION['felhasznalo']) && $_SESSION['felhasznalo']['felhasznalonev'] == $img['FELHASZNALO_FELHASZNALONEV']): ?>
                    <form action="controllers/delete_handler.php" method="post" onsubmit="return confirm('Biztosan törlöd a képet?');">
                        <input type="hidden" name="kep_id" value="<?= htmlspecialchars($img['ID']) ?>">
                        <button type="submit" class="delete-button">Törlés</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Új hozzászólás -->
        <?php if (isset($_SESSION['felhasznalo'])): ?>
            <form action="controllers/comment_handler.php" method="post" class="comment-form">
                <input type="hidden" name="kep_id" value="<?= htmlspecialchars($img['ID']) ?>">
                <textarea name="szoveg" rows="4" placeholder="Írj hozzászólást..." required></textarea><br>
                <button type="submit">Hozzászólás küldése</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Jelentkezz be</a> hozzászóláshoz!</p>
        <?php endif; ?>

        <!-- Hozzászólások -->
        <div class="comments-section">
            <h3>Hozzászólások</h3>
            <?php
            function renderComments($comments, $depth = 0, $parentKepId = 0) {
                foreach ($comments as $comment) {
                    $marginLeft = $depth * 30;
                    $dt_comment = DateTime::createFromFormat("Y-m-d H:i:s", $comment['datum'], new DateTimeZone('UTC'));
                    if ($dt_comment) {
                        $dt_comment->setTimezone(new DateTimeZone('Europe/Budapest'));
                        $localCommentTime = $dt_comment->format('Y.m.d H:i:s');
                    } else {
                        $localCommentTime = htmlspecialchars($comment['datum']);
                    }
                    ?>
                    <div class="comment" style="margin-left: <?= $marginLeft ?>px;">
                        <p>
                            <strong><?= htmlspecialchars($comment['felhasznalo_felhasznalonev']) ?></strong>
                            <small>(<?= $localCommentTime ?>)</small>
                            <?php if (isset($_SESSION['felhasznalo']) && $_SESSION['felhasznalo']['felhasznalonev'] == $comment['felhasznalo_felhasznalonev']): ?>
                                <!-- Törlés gomb Material Symbols kukával -->
                                <a href="controllers/comment_delete_handler.php?comment_id=<?= urlencode($comment['id']) ?>&kep_id=<?= urlencode($parentKepId) ?>"
                                   class="delete-comment"
                                   onclick="return confirm('Biztosan törlöd ezt a hozzászólást?');">
                                    <span class="material-symbols-outlined">delete</span>
                                </a>
                            <?php endif; ?>
                            <!-- Válasz ikon gomb -->
                            <?php if (isset($_SESSION['felhasznalo'])): ?>
                                <span class="material-symbols-outlined reply-icon"
                                      onclick="toggleReplyForm(<?= $comment['id'] ?>)">
                            reply
                        </span>
                            <?php endif; ?>
                        </p>
                        <p><?= nl2br(htmlspecialchars($comment['szoveg'])) ?></p>

                        <!-- Dinamikusan megjelenő válasz form -->
                        <form id="reply-form-<?= $comment['id'] ?>"
                              action="controllers/comment_handler.php"
                              method="post"
                              class="reply-form"
                              style="display: none;">
                            <input type="hidden" name="kep_id" value="<?= htmlspecialchars($parentKepId) ?>">
                            <input type="hidden" name="parent_id" value="<?= htmlspecialchars($comment['id']) ?>">
                            <textarea name="szoveg" rows="2" placeholder="Válasz írása..." required></textarea><br>
                            <button type="submit">Válasz küldése</button>
                        </form>

                        <?php if (!empty($comment['replies'])) {
                            renderComments($comment['replies'], $depth + 1, $parentKepId);
                        } ?>
                    </div>
                    <?php
                }
            }
            renderComments($commentTree, 0, $img['ID']);
            ?>
        </div>

        <!-- JavaScript a válasz mező dinamikus megjelenítéséhez -->
        <script>
            function toggleReplyForm(commentId) {
                var form = document.getElementById('reply-form-' + commentId);
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'block';
                } else {
                    form.style.display = 'none';
                }
            }
        </script>
    </div>
    <footer>
        <p>&copy; 2025 Fénykép Albumok. Minden jog fenntartva.</p>
    </footer>
</div>





















</body>
</html>
