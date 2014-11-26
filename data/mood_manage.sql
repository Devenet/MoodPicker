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

INSERT INTO settings(name, value) VALUES ('api_display_doc', '1');
INSERT INTO settings(name, value) VALUES ('api_request', '0');