-- 
-- Structure for table `wpisy`
-- 

DROP TABLE IF EXISTS `wpisy`;
CREATE TABLE IF NOT EXISTS `wpisy` (
  `id_wpisu` int(11) NOT NULL,
  `id_zawodnika` int(11) NOT NULL,
  `dystans` float NOT NULL,
  `data_wpisu` datetime NOT NULL,
  KEY `id_wpisu` (`id_wpisu`),
  KEY `id_zawodnika` (`id_zawodnika`),
  CONSTRAINT `wpisy_ibfk_1` FOREIGN KEY (`id_zawodnika`) REFERENCES `zawodnicy` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 
-- Structure for table `zawodnicy`
-- 

DROP TABLE IF EXISTS `zawodnicy`;
CREATE TABLE IF NOT EXISTS `zawodnicy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nick` varchar(30) NOT NULL,
  `data_dolaczenia` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nick` (`nick`)
) ENGINE=InnoDB AUTO_INCREMENT=467 DEFAULT CHARSET=utf8;

