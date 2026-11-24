-- GameHub v2 — révocation des sessions (SQLite)
-- Incrémenté à chaque changement de mot de passe : les autres sessions ouvertes deviennent invalides.
ALTER TABLE users ADD COLUMN session_version INTEGER NOT NULL DEFAULT 0;
