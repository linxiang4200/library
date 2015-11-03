DROP TABLE IF EXISTS `pre_library_book`;
CREATE TABLE IF NOT EXISTS `pre_library_book` (
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `translator` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `publisher` varchar(100) NOT NULL DEFAULT '' COMMENT '������',
  `pubdate` varchar(100) NOT NULL COMMENT '����ʱ��',
  `isbn10` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-10',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `binding` varchar(100) NOT NULL DEFAULT '' COMMENT 'װ֡',
  `price` varchar(100) NOT NULL COMMENT '�۸�',
  `pages` varchar(20) NOT NULL DEFAULT '' COMMENT 'ҳ��',
  `rating` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `author-intro` text NOT NULL DEFAULT '' COMMENT '���߼��',
  `summary` text NOT NULL DEFAULT '' COMMENT '���',
  `tags` text NOT NULL DEFAULT '' COMMENT 'TAG',
  `cids` text NOT NULL DEFAULT '' COMMENT '����ID',
  `category` text NOT NULL DEFAULT '' COMMENT '��������',
  `editor_recommendation` text NOT NULL DEFAULT '' COMMENT '�༭�Ƽ�',
  `attribute` text NOT NULL DEFAULT '' COMMENT '��������',
  `douban_id` varchar(20) NOT NULL DEFAULT '' COMMENT '����ID',
  `douban_image` varchar(255) NOT NULL DEFAULT '' COMMENT '����Image',
  `cover_image` varchar(255) NOT NULL DEFAULT '' COMMENT '����ͼƬ;',
  `dateline` int(10) unsigned NOT NULL COMMENT '���ݽ���ʱ��',
  `store_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�ݲ�����',
  `lend_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�ɽ�����',
  `lended_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�������',
  `circulation_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '��ͨ��',
  `reservation_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ԤԼ��',
  `last_circulation_timeline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�����ͨʱ��',
  PRIMARY KEY (`bid`),
  UNIQUE KEY `isbn10` (`isbn10`),
  UNIQUE KEY `isbn13` (`isbn13`)
) ENGINE=MyISAM COMMENT='ͼ���_ͼ��';

DROP TABLE IF EXISTS `pre_library_book_comment`;
CREATE TABLE  `pre_library_book_comment` (
  `bcid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '���',
  `bid` int(10) unsigned NOT NULL COMMENT 'ͼ��ID',
  `uid` int(10) unsigned NOT NULL COMMENT '����UID',
  `content` text NOT NULL DEFAULT '' COMMENT '����',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '��ʾ״̬\n(-1:����� 0:���� 1:����)',
  `dateline` int(10) unsigned NOT NULL COMMENT '���ݽ���ʱ��',
  PRIMARY KEY (`bcid`),
  KEY `bid` (`bid`)
) ENGINE=MyISAM COMMENT='ͼ���_ͼ��_����';

DROP TABLE IF EXISTS `pre_library_book_attachment`;
CREATE TABLE IF NOT EXISTS `pre_library_book_attachment` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) unsigned NOT NULL COMMENT 'ͼ��ID',
  `attachment` varchar(255) NOT NULL DEFAULT '' COMMENT '������ַ',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `isimage` tinyint(1) NOT NULL DEFAULT '0' COMMENT '�Ƿ���ͼƬ',
  `width` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '���',
  `dateline` int(10) unsigned NOT NULL COMMENT '����ʱ��',
  PRIMARY KEY (`aid`),
  KEY `bid` (`bid`),
  KEY `bid_aid` (`bid`,`aid`)
) ENGINE=MyISAM COMMENT='ͼ���_ͼ��_����';

DROP TABLE IF EXISTS `pre_library_store`;
CREATE TABLE IF NOT EXISTS `pre_library_store` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) unsigned NOT NULL COMMENT 'ͼ��ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `sno` varchar(50) NOT NULL DEFAULT '' COMMENT '�ݲ�����',
  `owner` varchar(255) NOT NULL DEFAULT '' COMMENT '��������Ϣ',
  `owner_uid` int(10) NOT NULL DEFAULT '0' COMMENT '������,uid',
  `warehouse` varchar(255) NOT NULL DEFAULT '' COMMENT '�ݲص�',
  `accession_number` varchar(255) NOT NULL DEFAULT '' COMMENT '��ȡ��',
  `status` int(4) NOT NULL DEFAULT '0' COMMENT '��ǰ״̬:0:�ڹݿɽ裻1:�����2��Ԥ����-1:����',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '��ǰ�����',
  `dateline_lend` int(10) unsigned NOT NULL COMMENT '���ʱ��',
  `dateline_return` int(10) unsigned NOT NULL COMMENT 'Ԥ�ƹ黹ʱ��',
  `store_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�ݲ�����',
  `lend_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�ɽ�����',
  `lended_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�������',
  `renew_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '���ڴ���',
  `dateline` int(10) unsigned NOT NULL COMMENT '���ݽ���ʱ��',
  UNIQUE KEY `sno` (`sno`),
  PRIMARY KEY (`sid`),
  KEY `bid` (`bid`),
  KEY `isbn13` (`isbn13`)
) ENGINE=MyISAM COMMENT='ͼ���_�ݲ�';

DROP TABLE IF EXISTS `pre_library_circulation`;
CREATE TABLE IF NOT EXISTS `pre_library_circulation` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) unsigned NOT NULL COMMENT 'ͼ��ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `sid` int(10) unsigned NOT NULL COMMENT '�ݲ�ID',
  `sno` varchar(50) NOT NULL DEFAULT '' COMMENT '�ݲ�����',
  `type` int(4) NOT NULL DEFAULT '0' COMMENT '��ͨ����:0:�����1:�黹',
  `uid` int(10) unsigned NOT NULL COMMENT '���� UID',
  `admin_uid` int(10) unsigned NOT NULL COMMENT '����Ա UID',
  `content` text NOT NULL DEFAULT '' COMMENT '��������',
  `dateline` int(10) unsigned NOT NULL COMMENT '����ʱ��',
  PRIMARY KEY (`cid`),
  KEY `bid` (`bid`),
  KEY `isbn13` (`isbn13`),
  KEY `sno` (`sno`),
  KEY `bid_cid` (`bid`,`cid`)
) ENGINE=MyISAM COMMENT='ͼ���_��ͨ';

DROP TABLE IF EXISTS `pre_library_reservation`;
CREATE TABLE IF NOT EXISTS `pre_library_reservation` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rno` varchar(50) NOT NULL DEFAULT '' COMMENT 'ԤԼ��',
  `bid` int(10) unsigned NOT NULL COMMENT 'ͼ��ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `uid` int(10) unsigned NOT NULL COMMENT 'ԤԼ�� UID',
  `order_number` int(10) unsigned NOT NULL COMMENT '�ŶӺ�',
  `admin_uid` int(10) unsigned NOT NULL COMMENT '����Ա UID',
  `status` int(4) NOT NULL DEFAULT '0' COMMENT '��ǰ״̬:0:�ύ���룻1:׼���鼮��2:�Ŷӣ�3���ȴ�ȡ�飻-1:����',
  `dateline_apply` int(10) unsigned NOT NULL COMMENT '�ύ����ʱ��',
  `dateline` int(10) unsigned NOT NULL COMMENT '����ʱ��',
  `logs` text NOT NULL DEFAULT '' COMMENT '������־',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `rno` (`rno`),
  KEY `bid` (`bid`),
  KEY `isbn13` (`isbn13`)
) ENGINE=MyISAM COMMENT='ͼ���_ԤԼ';

DROP TABLE IF EXISTS `pre_library_category`;
CREATE TABLE IF NOT EXISTS `pre_library_category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '����id',
  `cup` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�ϼ�����id',
  `name` char(50) NOT NULL DEFAULT '' COMMENT '����',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '��ʾ״̬\n(0:���� 1:����)',
  `displayorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '��ʾ˳��',
  PRIMARY KEY (`cid`),
  KEY `cup` (`cup`)
) ENGINE=MyISAM COMMENT='ͼ���_����';

DROP TABLE IF EXISTS `pre_library_bookthelf`;
CREATE TABLE IF NOT EXISTS `pre_library_bookthelf` (
  `btid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '���id',
  `uid` int(10) unsigned NOT NULL COMMENT '������UID',
  `name` char(255) NOT NULL DEFAULT '' COMMENT '����',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '��ʾ״̬\n(0:���� 1:����)',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�������\n(0:�ղؼ� 1:���� 2:�ڶ� 3:���� 4:���ﳵ)',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ͼ������',
  `dateline` int(10) unsigned NOT NULL COMMENT '���ݽ���ʱ��',
  `displayorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '��ʾ˳��',
  `summary` text NOT NULL DEFAULT '' COMMENT '���',
  PRIMARY KEY (`btid`),
  KEY `name` (`name`)
) ENGINE=MyISAM COMMENT='ͼ���_���';

DROP TABLE IF EXISTS `pre_library_bookthelf_item`;
CREATE TABLE IF NOT EXISTS `pre_library_bookthelf_item` (
  `btiid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '��Ŀ���',
  `btid` int(10) unsigned NOT NULL COMMENT '���id',
  `uid` int(10) unsigned NOT NULL COMMENT '������UID',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '�������\n(0:�ղؼ� 1:���� 2:�ڶ� 3:���� 4:���ﳵ)',
  `bid` int(10) unsigned NOT NULL COMMENT 'ͼ��ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '����',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '��ʾ״̬\n(0:���� 1:����)',
  `donate_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '����״̬\n(-1:��� 0:���� 1:����)',
  `donate_dateline` tinyint(4) NOT NULL DEFAULT '0' COMMENT '������Чʱ��',
  `dateline` int(10) unsigned NOT NULL COMMENT '���ݽ���ʱ��',
  `displayorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '��ʾ˳��',
  `summary` text NOT NULL DEFAULT '' COMMENT '���',
  PRIMARY KEY (`btiid`),
  KEY `btid` (`btid`)
) ENGINE=MyISAM COMMENT='ͼ���_����е�ͼ��';

DROP TABLE IF EXISTS `pre_library_member`;
CREATE TABLE IF NOT EXISTS `pre_library_member` (
  `uid` int(10) unsigned NOT NULL COMMENT 'UID',
  `username` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '�ֻ�',
  `referee_uid` int(10) unsigned NOT NULL COMMENT '�Ƽ���UID',
  `amount_lended` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '��ǰ�������',
  `amount_lended_all` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '��ʷ�������',
  `dateline` int(10) unsigned NOT NULL COMMENT '���ݽ���ʱ��',
  `receiver_address` varchar(255) NOT NULL DEFAULT '' COMMENT '�ջ���ַ',
  `receiver_name` varchar(50) NOT NULL DEFAULT '' COMMENT '�ջ�������',
  `receiver_phone` varchar(50) NOT NULL DEFAULT '' COMMENT '�ջ��˵绰',
  `receiver_qq` varchar(50) NOT NULL DEFAULT '' COMMENT '�ջ���QQ',
  `receiver_time` varchar(255) NOT NULL DEFAULT '' COMMENT '����ʱ��',
  `receiver_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '����Ҫ��',
  `deposit` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT 'Ѻ��',
  `cost` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '���Ѷ�',
  `balance` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '���',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`)
) ENGINE=MyISAM COMMENT='ͼ���_��Ա';
