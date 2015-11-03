DROP TABLE IF EXISTS `pre_library_book`;
CREATE TABLE IF NOT EXISTS `pre_library_book` (
  `bid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '书名',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '作者',
  `translator` varchar(255) NOT NULL DEFAULT '' COMMENT '译者',
  `publisher` varchar(100) NOT NULL DEFAULT '' COMMENT '出版社',
  `pubdate` varchar(100) NOT NULL COMMENT '出版时间',
  `isbn10` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-10',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `binding` varchar(100) NOT NULL DEFAULT '' COMMENT '装帧',
  `price` varchar(100) NOT NULL COMMENT '价格',
  `pages` varchar(20) NOT NULL DEFAULT '' COMMENT '页数',
  `rating` varchar(255) NOT NULL DEFAULT '' COMMENT '评分',
  `author-intro` text NOT NULL DEFAULT '' COMMENT '作者简介',
  `summary` text NOT NULL DEFAULT '' COMMENT '简介',
  `tags` text NOT NULL DEFAULT '' COMMENT 'TAG',
  `cids` text NOT NULL DEFAULT '' COMMENT '分类ID',
  `category` text NOT NULL DEFAULT '' COMMENT '分类文字',
  `editor_recommendation` text NOT NULL DEFAULT '' COMMENT '编辑推荐',
  `attribute` text NOT NULL DEFAULT '' COMMENT '其它属性',
  `douban_id` varchar(20) NOT NULL DEFAULT '' COMMENT '豆瓣ID',
  `douban_image` varchar(255) NOT NULL DEFAULT '' COMMENT '豆瓣Image',
  `cover_image` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图片;',
  `dateline` int(10) unsigned NOT NULL COMMENT '数据建立时间',
  `store_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '馆藏总数',
  `lend_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可借数量',
  `lended_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '借出数量',
  `circulation_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '流通数',
  `reservation_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '预约数',
  `last_circulation_timeline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后流通时间',
  PRIMARY KEY (`bid`),
  UNIQUE KEY `isbn10` (`isbn10`),
  UNIQUE KEY `isbn13` (`isbn13`)
) ENGINE=MyISAM COMMENT='图书馆_图书';

DROP TABLE IF EXISTS `pre_library_book_comment`;
CREATE TABLE  `pre_library_book_comment` (
  `bcid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `bid` int(10) unsigned NOT NULL COMMENT '图书ID',
  `uid` int(10) unsigned NOT NULL COMMENT '作者UID',
  `content` text NOT NULL DEFAULT '' COMMENT '内容',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '显示状态\n(-1:待审核 0:隐藏 1:正常)',
  `dateline` int(10) unsigned NOT NULL COMMENT '数据建立时间',
  PRIMARY KEY (`bcid`),
  KEY `bid` (`bid`)
) ENGINE=MyISAM COMMENT='图书馆_图书_评论';

DROP TABLE IF EXISTS `pre_library_book_attachment`;
CREATE TABLE IF NOT EXISTS `pre_library_book_attachment` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) unsigned NOT NULL COMMENT '图书ID',
  `attachment` varchar(255) NOT NULL DEFAULT '' COMMENT '附件地址',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `isimage` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是图片',
  `width` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '宽度',
  `dateline` int(10) unsigned NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`aid`),
  KEY `bid` (`bid`),
  KEY `bid_aid` (`bid`,`aid`)
) ENGINE=MyISAM COMMENT='图书馆_图书_附件';

DROP TABLE IF EXISTS `pre_library_store`;
CREATE TABLE IF NOT EXISTS `pre_library_store` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) unsigned NOT NULL COMMENT '图书ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '书名',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `sno` varchar(50) NOT NULL DEFAULT '' COMMENT '馆藏条码',
  `owner` varchar(255) NOT NULL DEFAULT '' COMMENT '所有者信息',
  `owner_uid` int(10) NOT NULL DEFAULT '0' COMMENT '所有者,uid',
  `warehouse` varchar(255) NOT NULL DEFAULT '' COMMENT '馆藏地',
  `accession_number` varchar(255) NOT NULL DEFAULT '' COMMENT '索取号',
  `status` int(4) NOT NULL DEFAULT '0' COMMENT '当前状态:0:在馆可借；1:借出；2；预订；-1:过期',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前借出人',
  `dateline_lend` int(10) unsigned NOT NULL COMMENT '借出时间',
  `dateline_return` int(10) unsigned NOT NULL COMMENT '预计归还时间',
  `store_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '馆藏数量',
  `lend_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可借阅数',
  `lended_amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '借出数量',
  `renew_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '续期次数',
  `dateline` int(10) unsigned NOT NULL COMMENT '数据建立时间',
  UNIQUE KEY `sno` (`sno`),
  PRIMARY KEY (`sid`),
  KEY `bid` (`bid`),
  KEY `isbn13` (`isbn13`)
) ENGINE=MyISAM COMMENT='图书馆_馆藏';

DROP TABLE IF EXISTS `pre_library_circulation`;
CREATE TABLE IF NOT EXISTS `pre_library_circulation` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid` int(10) unsigned NOT NULL COMMENT '图书ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '书名',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `sid` int(10) unsigned NOT NULL COMMENT '馆藏ID',
  `sno` varchar(50) NOT NULL DEFAULT '' COMMENT '馆藏条码',
  `type` int(4) NOT NULL DEFAULT '0' COMMENT '流通类型:0:借出；1:归还',
  `uid` int(10) unsigned NOT NULL COMMENT '读者 UID',
  `admin_uid` int(10) unsigned NOT NULL COMMENT '管理员 UID',
  `content` text NOT NULL DEFAULT '' COMMENT '操作内容',
  `dateline` int(10) unsigned NOT NULL COMMENT '操作时间',
  PRIMARY KEY (`cid`),
  KEY `bid` (`bid`),
  KEY `isbn13` (`isbn13`),
  KEY `sno` (`sno`),
  KEY `bid_cid` (`bid`,`cid`)
) ENGINE=MyISAM COMMENT='图书馆_流通';

DROP TABLE IF EXISTS `pre_library_reservation`;
CREATE TABLE IF NOT EXISTS `pre_library_reservation` (
  `rid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rno` varchar(50) NOT NULL DEFAULT '' COMMENT '预约号',
  `bid` int(10) unsigned NOT NULL COMMENT '图书ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '书名',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `uid` int(10) unsigned NOT NULL COMMENT '预约人 UID',
  `order_number` int(10) unsigned NOT NULL COMMENT '排队号',
  `admin_uid` int(10) unsigned NOT NULL COMMENT '管理员 UID',
  `status` int(4) NOT NULL DEFAULT '0' COMMENT '当前状态:0:提交申请；1:准备书籍；2:排队；3；等待取书；-1:过期',
  `dateline_apply` int(10) unsigned NOT NULL COMMENT '提交申请时间',
  `dateline` int(10) unsigned NOT NULL COMMENT '操作时间',
  `logs` text NOT NULL DEFAULT '' COMMENT '操作日志',
  PRIMARY KEY (`rid`),
  UNIQUE KEY `rno` (`rno`),
  KEY `bid` (`bid`),
  KEY `isbn13` (`isbn13`)
) ENGINE=MyISAM COMMENT='图书馆_预约';

DROP TABLE IF EXISTS `pre_library_category`;
CREATE TABLE IF NOT EXISTS `pre_library_category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `cup` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类id',
  `name` char(50) NOT NULL DEFAULT '' COMMENT '名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示状态\n(0:隐藏 1:正常)',
  `displayorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  PRIMARY KEY (`cid`),
  KEY `cup` (`cup`)
) ENGINE=MyISAM COMMENT='图书馆_分类';

DROP TABLE IF EXISTS `pre_library_bookthelf`;
CREATE TABLE IF NOT EXISTS `pre_library_bookthelf` (
  `btid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '书架id',
  `uid` int(10) unsigned NOT NULL COMMENT '所有者UID',
  `name` char(255) NOT NULL DEFAULT '' COMMENT '名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示状态\n(0:隐藏 1:正常)',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '书架类型\n(0:收藏夹 1:读过 2:在读 3:捐赠 4:购物车)',
  `amount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '图书数量',
  `dateline` int(10) unsigned NOT NULL COMMENT '数据建立时间',
  `displayorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `summary` text NOT NULL DEFAULT '' COMMENT '简介',
  PRIMARY KEY (`btid`),
  KEY `name` (`name`)
) ENGINE=MyISAM COMMENT='图书馆_书架';

DROP TABLE IF EXISTS `pre_library_bookthelf_item`;
CREATE TABLE IF NOT EXISTS `pre_library_bookthelf_item` (
  `btiid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '项目编号',
  `btid` int(10) unsigned NOT NULL COMMENT '书架id',
  `uid` int(10) unsigned NOT NULL COMMENT '所有者UID',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '书架类型\n(0:收藏夹 1:读过 2:在读 3:捐赠 4:购物车)',
  `bid` int(10) unsigned NOT NULL COMMENT '图书ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '书名',
  `isbn13` varchar(20) NOT NULL DEFAULT '' COMMENT 'ISBN-13',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示状态\n(0:隐藏 1:正常)',
  `donate_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '捐赠状态\n(-1:审核 0:隐藏 1:正常)',
  `donate_dateline` tinyint(4) NOT NULL DEFAULT '0' COMMENT '捐赠生效时间',
  `dateline` int(10) unsigned NOT NULL COMMENT '数据建立时间',
  `displayorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '显示顺序',
  `summary` text NOT NULL DEFAULT '' COMMENT '简介',
  PRIMARY KEY (`btiid`),
  KEY `btid` (`btid`)
) ENGINE=MyISAM COMMENT='图书馆_书架中的图书';

DROP TABLE IF EXISTS `pre_library_member`;
CREATE TABLE IF NOT EXISTS `pre_library_member` (
  `uid` int(10) unsigned NOT NULL COMMENT 'UID',
  `username` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `referee_uid` int(10) unsigned NOT NULL COMMENT '推荐人UID',
  `amount_lended` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当前借出数量',
  `amount_lended_all` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '历史借出数量',
  `dateline` int(10) unsigned NOT NULL COMMENT '数据建立时间',
  `receiver_address` varchar(255) NOT NULL DEFAULT '' COMMENT '收货地址',
  `receiver_name` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `receiver_phone` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人电话',
  `receiver_qq` varchar(50) NOT NULL DEFAULT '' COMMENT '收货人QQ',
  `receiver_time` varchar(255) NOT NULL DEFAULT '' COMMENT '配送时间',
  `receiver_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '其他要求',
  `deposit` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '押金',
  `cost` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '消费额',
  `balance` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '余额',
  PRIMARY KEY (`uid`),
  KEY `username` (`username`)
) ENGINE=MyISAM COMMENT='图书馆_会员';
