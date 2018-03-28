<?php
require '../mysql_connect.php';

echo $mysqlDbc->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";

$drop_table = "DROP TABLE IF EXISTS users";

$mysqlDbc->exec($drop_table);

$create_table = 'CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(200) DEFAULT NULL,
  `last_name` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT '',
  `password` varchar(300) DEFAULT NULL,
  `unique_id` varchar(200) DEFAULT NULL,
  `cob_session` varchar(500) DEFAULT NULL,
  `user_session` varchar(500) DEFAULT NULL,
  `fastlink_value` varchar(500) DEFAULT NULL,
  `cob_session_created_at` timestamp NULL DEFAULT NULL,
  `user_session_created_at` timestamp NULL DEFAULT NULL,
  `super_user` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;';

$mysqlDbc->exec($create_table);