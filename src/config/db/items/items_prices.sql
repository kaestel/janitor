CREATE TABLE `SITE_DB`.`items_prices` (
  `id` int(11) NOT NULL auto_increment,
  `item_id` int(11) NOT NULL,

  `price` float NOT NULL,
  `currency` varchar(3) NOT NULL,
  `vatrate_id` int(11) NOT NULL,

  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  KEY `currency` (`currency`),
  KEY `vatrate_id` (`vatrate_id`),

  CONSTRAINT `items_prices_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `SITE_DB`.`items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `items_prices_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `SITE_DB`.`currencies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `items_prices_ibfk_3` FOREIGN KEY (`vatrate_id`) REFERENCES `SITE_DB`.`vatrates` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
