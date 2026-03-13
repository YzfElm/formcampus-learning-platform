-- ============================================================
--  FormCampus — Système d'évaluation par étoiles
--  INSTRUCTIONS : Importez ce fichier dans phpMyAdmin
--  (la base formcampus doit déjà exister)
-- ============================================================

USE formcampus;

-- Table des évaluations individuelles (1 note par utilisateur par formation)
CREATE TABLE IF NOT EXISTS evaluations (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    formation_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    note         TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_evaluation (formation_id, utilisateur_id),
    FOREIGN KEY (formation_id)   REFERENCES formations(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id)      ON DELETE CASCADE
) ENGINE=InnoDB;

-- Colonne note_moyenne en cache sur la table formations (mise à jour par trigger)
ALTER TABLE formations
    ADD COLUMN IF NOT EXISTS note_moyenne DECIMAL(3,2) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS nb_evaluations INT NOT NULL DEFAULT 0;

-- Trigger : recalcule note_moyenne après INSERT ou UPDATE d'une évaluation
DELIMITER $$

CREATE TRIGGER trg_eval_after_insert
AFTER INSERT ON evaluations
FOR EACH ROW
BEGIN
    UPDATE formations
    SET note_moyenne   = (SELECT AVG(note)   FROM evaluations WHERE formation_id = NEW.formation_id),
        nb_evaluations = (SELECT COUNT(*)    FROM evaluations WHERE formation_id = NEW.formation_id)
    WHERE id = NEW.formation_id;
END$$

CREATE TRIGGER trg_eval_after_update
AFTER UPDATE ON evaluations
FOR EACH ROW
BEGIN
    UPDATE formations
    SET note_moyenne   = (SELECT AVG(note)   FROM evaluations WHERE formation_id = NEW.formation_id),
        nb_evaluations = (SELECT COUNT(*)    FROM evaluations WHERE formation_id = NEW.formation_id)
    WHERE id = NEW.formation_id;
END$$

DELIMITER ;
