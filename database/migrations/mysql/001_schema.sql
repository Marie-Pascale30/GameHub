-- GameHub v2 — schéma initial (MySQL 8 / MariaDB 10.6+)

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(30)  NOT NULL UNIQUE,
    email         VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at    DATETIME     NOT NULL,
    updated_at    DATETIME     NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE games (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(120) NOT NULL UNIQUE,
    developer    VARCHAR(120) NOT NULL,
    release_year SMALLINT     NOT NULL,
    synopsis     TEXT         NOT NULL,
    created_at   DATETIME     NOT NULL,
    updated_at   DATETIME     NULL,
    INDEX idx_games_year (release_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE genres (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL UNIQUE,
    slug VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE platforms (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(40) NOT NULL UNIQUE,
    slug       VARCHAR(40) NOT NULL UNIQUE,
    short_name VARCHAR(12) NOT NULL,
    sort_order INT         NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE game_genre (
    game_id  INT UNSIGNED NOT NULL,
    genre_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (game_id, genre_id),
    INDEX idx_game_genre_genre (genre_id),
    FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE game_platform (
    game_id     INT UNSIGNED NOT NULL,
    platform_id INT UNSIGNED NOT NULL,
    position    INT          NOT NULL DEFAULT 0,
    PRIMARY KEY (game_id, platform_id),
    INDEX idx_game_platform_platform (platform_id),
    FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES platforms (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE favorites (
    user_id    INT UNSIGNED NOT NULL,
    game_id    INT UNSIGNED NOT NULL,
    created_at DATETIME     NOT NULL,
    PRIMARY KEY (user_id, game_id),
    INDEX idx_favorites_game (game_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    game_id    INT UNSIGNED NOT NULL,
    rating     TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
    body       TEXT         NULL,
    created_at DATETIME     NOT NULL,
    updated_at DATETIME     NULL,
    UNIQUE KEY uq_reviews_user_game (user_id, game_id),
    INDEX idx_reviews_game (game_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token_hash CHAR(64)     NOT NULL UNIQUE,
    expires_at INT UNSIGNED NOT NULL,
    used_at    INT UNSIGNED NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX idx_password_resets_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE rate_limits (
    id       BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rate_key CHAR(64)     NOT NULL,
    hit_at   INT UNSIGNED NOT NULL,
    INDEX idx_rate_limits_key (rate_key, hit_at)
) ENGINE=InnoDB;
