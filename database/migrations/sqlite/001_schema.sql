-- GameHub v2 — schéma initial (SQLite)

CREATE TABLE users (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    username      VARCHAR(30)  NOT NULL UNIQUE,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(10)  NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at    DATETIME     NOT NULL,
    updated_at    DATETIME     NULL
);

CREATE TABLE games (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        VARCHAR(120) NOT NULL UNIQUE,
    developer    VARCHAR(120) NOT NULL,
    release_year INTEGER      NOT NULL,
    synopsis     TEXT         NOT NULL,
    created_at   DATETIME     NOT NULL,
    updated_at   DATETIME     NULL
);
CREATE INDEX idx_games_year ON games (release_year);

CREATE TABLE genres (
    id   INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(40) NOT NULL UNIQUE,
    slug VARCHAR(40) NOT NULL UNIQUE
);

CREATE TABLE platforms (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       VARCHAR(40) NOT NULL UNIQUE,
    slug       VARCHAR(40) NOT NULL UNIQUE,
    short_name VARCHAR(12) NOT NULL,
    sort_order INTEGER     NOT NULL DEFAULT 0
);

CREATE TABLE game_genre (
    game_id  INTEGER NOT NULL REFERENCES games (id) ON DELETE CASCADE,
    genre_id INTEGER NOT NULL REFERENCES genres (id) ON DELETE CASCADE,
    PRIMARY KEY (game_id, genre_id)
);
CREATE INDEX idx_game_genre_genre ON game_genre (genre_id);

CREATE TABLE game_platform (
    game_id     INTEGER NOT NULL REFERENCES games (id) ON DELETE CASCADE,
    platform_id INTEGER NOT NULL REFERENCES platforms (id) ON DELETE CASCADE,
    position    INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY (game_id, platform_id)
);
CREATE INDEX idx_game_platform_platform ON game_platform (platform_id);

CREATE TABLE favorites (
    user_id    INTEGER  NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    game_id    INTEGER  NOT NULL REFERENCES games (id) ON DELETE CASCADE,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, game_id)
);
CREATE INDEX idx_favorites_game ON favorites (game_id);

CREATE TABLE reviews (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER  NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    game_id    INTEGER  NOT NULL REFERENCES games (id) ON DELETE CASCADE,
    rating     INTEGER  NOT NULL CHECK (rating BETWEEN 1 AND 5),
    body       TEXT     NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    UNIQUE (user_id, game_id)
);
CREATE INDEX idx_reviews_game ON reviews (game_id);

CREATE TABLE password_resets (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER  NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at INTEGER  NOT NULL,
    used_at    INTEGER  NULL,
    created_at INTEGER  NOT NULL
);
CREATE INDEX idx_password_resets_user ON password_resets (user_id);

CREATE TABLE rate_limits (
    id       INTEGER PRIMARY KEY AUTOINCREMENT,
    rate_key CHAR(64) NOT NULL,
    hit_at   INTEGER  NOT NULL
);
CREATE INDEX idx_rate_limits_key ON rate_limits (rate_key, hit_at);
