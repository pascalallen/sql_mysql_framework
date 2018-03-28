<?php
require '../mysql_connect.php';

echo $mysqlDbc->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";

$drop_table = "DROP TABLE IF EXISTS logs";

$mysqlDbc->exec($drop_table);

$create_table = 'CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `created_at` varchar(200) DEFAULT NULL,
  `ip_address` varchar(200) DEFAULT NULL,
  `user_agent` longtext,
  `searched_for` longtext,
  `view` varchar(45) DEFAULT NULL,
  `log_type` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;';

$mysqlDbc->exec($create_table);