CREATE TABLE IF NOT EXISTS `flagged` (
  `keyword` varchar(200) NOT NULL,
  `clicks` int(10) NOT NULL default '0',
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `reason` text,
  `addr` varchar(200) default NULL,
  PRIMARY KEY  (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

