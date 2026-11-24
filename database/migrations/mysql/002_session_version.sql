-- GameHub v2 — révocation des sessions (MySQL)
-- Incrémenté à chaque changement de mot de passe : les autres sessions ouvertes deviennent invalides.
ALTER TABLE users ADD COLUMN session_version INT UNSIGNED NOT NULL DEFAULT 0;
