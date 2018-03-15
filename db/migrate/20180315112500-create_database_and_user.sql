USE mysql;

CREATE DATABASE youtube_stats
    DEFAULT CHARACTER SET 'utf8'
    DEFAULT COLLATE 'utf8_unicode_ci';

CREATE USER 'youtube_stats_app'@'%'
    IDENTIFIED BY '********';

GRANT ALL
    ON youtube_stats.*
    TO 'youtube_stats_app'@'%';
