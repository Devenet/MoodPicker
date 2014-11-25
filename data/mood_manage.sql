CREATE TABLE `users` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `email` TEXT NOT NULL,
    `password` TEXT NOT NULL,
    `last_login` INTEGER NOT NULL,
    `last_ip` INTEGER DEFAULT '0.0.0.0'
);

CREATE TABLE `api_request` (
    `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `email` TEXT NOT NULL,
    `timestamp` INTEGER NOT NULL,
    `api_id` INTEGER NULL,
    `approbation` INTEGER DEFAULT 0
);
