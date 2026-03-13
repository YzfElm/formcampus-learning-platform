<?php
/**
 * includes/rating.php — Composant étoiles réutilisable
 *
 * Utilisation :
 *   include 'includes/rating.php';
 *   render_rating($formation_id, $note_moyenne, $nb_evaluations, $user_note, $interactive);
 *
 * Paramètres :
 *   $formation_id    (int)   — ID de la formation
 *   $note_moyenne    (float) — Note moyenne (ex: 3.7), null si aucune note
 *   $nb_evaluations  (int)   — Nombre total de votes
 *   $user_note       (int)   — Note donnée par l'utilisateur connecté (0 = pas encore noté)
 *   $interactive     (bool)  — true = étoiles cliquables, false = lecture seule
 */

function render_rating(
    int   $formation_id,
    ?float $note_moyenne,
    int   $nb_evaluations,
    int   $user_note   = 0,
    bool  $interactive = false
): void {
    $display_note  = $note_moyenne ?? 0;
    $is_logged     = function_exists('is_logged_in') && is_logged_in();
    $can_interact  = $interactive && $is_logged;
    $unique_id     = 'rating-' . $formation_id;
    ?>
    <div class="fc-rating"
         id="<?= $unique_id ?>"
         data-formation-id="<?= $formation_id ?>"
         data-user-note="<?= $user_note ?>"
         aria-label="Note : <?= $display_note > 0 ? number_format($display_note, 1) . '/5' : 'Aucune note' ?>">

        <div class="fc-stars <?= $can_interact ? 'fc-stars--interactive' : '' ?>"
             title="<?= $can_interact ? 'Cliquez pour noter' : ($display_note > 0 ? number_format($display_note,1).'/5' : 'Aucune note') ?>">
            <?php for ($i = 1; $i <= 5; $i++):
                // Étoile pleine, demi ou vide selon la note moyenne
                if ($display_note >= $i) {
                    $cls = 'fc-star--full';
                } elseif ($display_note >= $i - 0.5) {
                    $cls = 'fc-star--half';
                } else {
                    $cls = 'fc-star--empty';
                }
                // Si l'utilisateur a déjà noté, on affiche sa note en surbrillance
                if ($user_note > 0 && $i <= $user_note) {
                    $cls = 'fc-star--user';
                }
            ?>
            <span class="fc-star <?= $cls ?>"
                  data-value="<?= $i ?>"
                  <?= $can_interact ? 'role="button" tabindex="0"' : '' ?>>
                <?php if ($cls === 'fc-star--half'): ?>
                    &#9734;<!-- demi-étoile via CSS -->
                <?php elseif ($cls === 'fc-star--empty'): ?>
                    &#9734;
                <?php else: ?>
                    &#9733;
                <?php endif; ?>
            </span>
            <?php endfor; ?>
        </div>

        <span class="fc-rating-meta">
            <?php if ($display_note > 0): ?>
                <strong><?= number_format($display_note, 1) ?></strong>
                <span>(<?= $nb_evaluations ?> avis)</span>
            <?php else: ?>
                <span>Aucune note</span>
            <?php endif; ?>
        </span>

        <?php if ($interactive && !$is_logged): ?>
            <a href="login.php" class="fc-rating-login">Connectez-vous pour noter</a>
        <?php endif; ?>

        <?php if ($can_interact): ?>
            <span class="fc-rating-feedback" style="display:none;"></span>
        <?php endif; ?>
    </div>
    <?php
}
?>
