-- ============================================================
--  Smart Fridge & AI Chef  —  Veritabani Kurulumu (cok kullanicili)
--  phpMyAdmin > SQL sekmesine yapistirip "Calistir" de.
-- ============================================================

CREATE DATABASE IF NOT EXISTS akilli_dolap
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE akilli_dolap;

-- Kullanicilar (giris sistemi)
CREATE TABLE IF NOT EXISTS users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)   NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    created_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- Dolaptaki malzemeler — her satir bir kullaniciya ait (user_id)
CREATE TABLE IF NOT EXISTS inventory (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT            NOT NULL,
    name        VARCHAR(100)   NOT NULL,
    quantity    DECIMAL(10,2)  NOT NULL DEFAULT 1,
    unit        VARCHAR(30)    NOT NULL DEFAULT 'adet',
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Gecmis tarifler — her kullanicinin "yaptim" dedigi tarifler burada birikir
CREATE TABLE IF NOT EXISTS recipes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT            NOT NULL,
    title       VARCHAR(200)   NOT NULL,
    data        TEXT           NOT NULL,
    created_at  TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
