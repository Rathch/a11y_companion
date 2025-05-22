CREATE TABLE sys_file_metadata (is_decorative tinyint(4) DEFAULT '0' NOT NULL);
CREATE TABLE sys_file_reference (is_decorative tinyint(4) DEFAULT '0' NOT NULL);
#
# Table structure for table 'tx_a11ycompanion_blacklistword'
#
CREATE TABLE tx_a11ycompanion_blacklistword (
uid int(11) NOT NULL auto_increment,
pid int(11) DEFAULT '0' NOT NULL,
tstamp int(11) DEFAULT '0' NOT NULL,
crdate int(11) DEFAULT '0' NOT NULL,
cruser_id int(11) DEFAULT '0' NOT NULL,
deleted tinyint(4) DEFAULT '0' NOT NULL,
hidden tinyint(4) DEFAULT '0' NOT NULL,
word varchar(255) DEFAULT '' NOT NULL,
PRIMARY KEY (uid)
);