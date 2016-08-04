CREATE TABLE `users` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `email` TEXT NOT NULL UNIQUE,
    `password` TEXT NOT NULL,
    `last_login` INTEGER DEFAULT NULL,
    `last_ip` INTEGER DEFAULT '0.0.0.0',
    `privilege` INTEGER DEFAULT '0'
);

CREATE TABLE `settings` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `name` TEXT NOT NULL UNIQUE,
    `value` TEXT NOT NULL
);

CREATE TABLE `api_request` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `email` TEXT NOT NULL UNIQUE,
    `timestamp` INTEGER NOT NULL,
    `api_id` INTEGER NULL,
    `approbation` INTEGER DEFAULT '0'
);

CREATE TABLE `tokens` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `token` TEXT NOT NULL,
    `expire` INTEGER NOT NULL,
    `api_key` TEXT NOT NULL
);

CREATE TABLE `credentials` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `api_key` TEXT NOT NULL UNIQUE,
    `api_token` TEXT NOT NULL,
    `api_name` TEXT NOT NULL,
    `last_timestamp` INTEGER NULL,
    `last_ip` TEXT  DEFAULT '0.0.0.0',
    `count` INTEGER DEFAULT 0
);
