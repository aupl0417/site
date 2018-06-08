-- --------------------------------------------------------
-- 主机:                           192.168.3.203
-- 服务器版本:                        10.0.25-MariaDB-wsrep - MariaDB Server, wsrep_25.13.raf7f02e
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 dtmall.ylh_swoole_crontab 结构
CREATE TABLE IF NOT EXISTS `ylh_swoole_crontab` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `atime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `etime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `status` tinyint(5) unsigned NOT NULL DEFAULT '0' COMMENT '1正常  2 暂停  0 删除',
  `taskid` varchar(32) NOT NULL COMMENT '任务ID',
  `taskname` varchar(32) NOT NULL COMMENT '任务名称',
  `rule` text NOT NULL COMMENT '规则 可以是crontab规则也可以是json类型的精确时间任务',
  `unique` tinyint(5) unsigned NOT NULL DEFAULT '0' COMMENT '0 唯一任务 大于0表示同时可并行的任务进程个数',
  `execute` varchar(50) NOT NULL COMMENT '运行这个任务的类',
  `args` text NOT NULL COMMENT '任务参数',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='定时任务和队列';

-- 正在导出表  dtmall.ylh_swoole_crontab 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `ylh_swoole_crontab` DISABLE KEYS */;
REPLACE INTO `ylh_swoole_crontab` (`id`, `atime`, `etime`, `status`, `taskid`, `taskname`, `rule`, `unique`, `execute`, `args`, `remark`) VALUES
	(4, '2016-10-27 14:06:04', '2016-11-01 10:44:43', 1, '', '入列：超时未付款的订单', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'buyer_add_orders\');', ''),
	(5, '2016-10-27 14:06:04', '2016-11-01 10:44:46', 1, '', '入列：买家长时间未确认收货，默认自动确认收货', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'buyer_confirm_orders\');', ''),
	(6, '2016-10-27 14:06:04', '2016-11-01 10:44:47', 1, '', '入列：买家长时间未确认收货，默认自动确认收货', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'seller_send_express\');', ''),
	(7, '2016-10-27 14:06:04', '2016-11-01 10:44:51', 1, '', '入列：已发货，退款，卖家长时间未响应，默认同意退款', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'buyer_refund_add\');', ''),
	(8, '2016-10-27 14:06:04', '2016-11-01 10:44:55', 1, '', '入列：退款被拒绝后买家长时间未响应，默认取消退款', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'seller_not_accept\');', ''),
	(9, '2016-10-27 14:06:04', '2016-11-01 10:44:57', 1, '', '入列：修改退款后卖家长时间未响应，默认为同意退款', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'buyer_refund_edit\');', ''),
	(10, '2016-10-27 14:06:04', '2016-11-01 10:44:58', 1, '', '入列：卖家同意退货，买家长时间未响应默认取消退款', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'seller_accept\');', ''),
	(11, '2016-10-27 14:06:04', '2016-11-01 10:45:00', 1, '', '入列：买家寄回退货，卖家长时间未确认，默认无异议自动退款', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'buyer_send_express\');', ''),
	(12, '2016-10-27 14:06:04', '2016-11-01 10:45:01', 1, '', '入列：超时未评价订单', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'buyer_rate\');', ''),
	(13, '2016-10-27 14:06:04', '2016-11-01 10:45:03', 1, '', '入列：商品主图搬家', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'goods_images\');', ''),
	(14, '2016-10-27 14:06:04', '2016-11-01 10:45:04', 1, '', '入列：库存主图搬家', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'goods_attr_list_images\');', ''),
	(15, '2016-10-27 14:06:04', '2016-11-01 15:01:26', 1, '', '入列：售后卖家长时间不操作则改为同意售后', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'service_seller_accept\');', ''),
	(16, '2016-10-27 14:06:04', '2016-11-01 15:01:29', 1, '', '入列：售后卖家长时间不确认收货，则改为已确认收货', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'service_seller_confirm\');', ''),
	(17, '2016-10-27 14:06:04', '2016-11-01 15:01:35', 1, '', '入列：售后买家长时间不确认收货，则改为已确认收货', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'service_buyer_confirm\');', ''),
	(18, '2016-10-27 14:06:04', '2016-11-01 15:01:41', 1, '', '入列：售后买家长时间不发货，则自动取消当前售后', '* * * * *', 1, 'TrjPut', 'return array(\'type\'=&gt;\'service_buyer_express\');', '');
/*!40000 ALTER TABLE `ylh_swoole_crontab` ENABLE KEYS */;


-- 导出  表 dtmall.ylh_swoole_queue 结构
CREATE TABLE IF NOT EXISTS `ylh_swoole_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `atime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `etime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `status` tinyint(5) unsigned NOT NULL DEFAULT '1' COMMENT '1正常  2 暂停  0 删除',
  `worker_id` int(10) unsigned NOT NULL COMMENT '所属worker',
  `name` varchar(50) NOT NULL COMMENT '队列标题',
  `tube` varchar(50) NOT NULL COMMENT '队列名称',
  `worker_num` tinyint(3) unsigned NOT NULL COMMENT '0 唯一任务 大于0表示同时可并行的任务进程个数',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tube` (`tube`),
  KEY `FK_ylh_swoole_queue_ylh_swoole_worker` (`worker_id`),
  CONSTRAINT `FK_ylh_swoole_queue_ylh_swoole_worker` FOREIGN KEY (`worker_id`) REFERENCES `ylh_swoole_worker` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='队列设置';

-- 正在导出表  dtmall.ylh_swoole_queue 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `ylh_swoole_queue` DISABLE KEYS */;
REPLACE INTO `ylh_swoole_queue` (`id`, `atime`, `etime`, `status`, `worker_id`, `name`, `tube`, `worker_num`, `remark`) VALUES
	(5, '2016-11-01 09:39:00', '2016-11-01 09:39:00', 1, 5, '关闭超时未付款的订单', 'buyer_add_orders', 5, ''),
	(7, '2016-11-01 09:39:00', '2016-11-01 11:02:04', 1, 5, '买家长时间未确认收货，默认自动确认收货', 'buyer_confirm_orders', 20, ''),
	(8, '2016-11-01 09:39:00', '2016-11-01 11:00:32', 1, 5, '未发货退款，卖家长时间未响应，默认同意退款', 'seller_send_express', 5, ''),
	(9, '2016-11-01 09:39:00', '2016-11-01 11:00:27', 1, 5, '已发货，退款，卖家长时间未响应，默认同意退款', 'buyer_refund_add', 5, ''),
	(10, '2016-11-01 09:39:00', '2016-11-01 11:00:22', 1, 5, '退款被拒绝后买家长时间未响应，默认取消退款', 'seller_not_accept', 5, ''),
	(11, '2016-11-01 09:39:00', '2016-11-01 11:00:16', 1, 5, '修改退款后卖家长时间未响应，默认为同意退款', 'buyer_refund_edit', 5, ''),
	(12, '2016-11-01 09:39:00', '2016-11-01 11:00:10', 1, 5, '卖家同意退货，买家长时间未响应默认取消退款', 'seller_accept', 5, ''),
	(13, '2016-11-01 09:39:00', '2016-11-01 11:00:05', 1, 5, '买家寄回退货，卖家长时间未确认，默认无异议自动退款', 'buyer_send_express', 5, ''),
	(14, '2016-11-01 09:39:00', '2016-11-01 11:01:15', 1, 5, '超时未评价订单', 'buyer_rate', 10, ''),
	(15, '2016-11-01 09:39:00', '2016-11-01 11:02:11', 1, 5, '商品主图搬家', 'goods_images', 5, ''),
	(16, '2016-11-01 09:39:00', '2016-11-01 11:02:16', 1, 5, '库存主图搬家', 'goods_attr_list_images', 5, ''),
	(17, '2016-11-01 09:39:00', '2016-11-01 14:59:53', 1, 5, '售后卖家长时间不操作则改为同意售后', 'service_seller_accept', 5, ''),
	(18, '2016-11-01 09:39:00', '2016-11-01 14:59:58', 1, 5, '售后卖家长时间不确认收货，则改为已确认收货', 'service_seller_confirm', 5, ''),
	(19, '2016-11-01 09:39:00', '2016-11-01 14:59:59', 1, 5, '售后买家长时间不确认收货，则改为已确认收货', 'service_buyer_confirm', 5, ''),
	(20, '2016-11-01 09:39:00', '2016-11-01 15:00:04', 1, 5, '售后买家长时间不发货，则自动取消当前售后', 'service_buyer_express', 5, '');
/*!40000 ALTER TABLE `ylh_swoole_queue` ENABLE KEYS */;


-- 导出  表 dtmall.ylh_swoole_worker 结构
CREATE TABLE IF NOT EXISTS `ylh_swoole_worker` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `atime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `etime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `status` tinyint(5) unsigned NOT NULL DEFAULT '1' COMMENT '1正常  2 暂停  0 删除',
  `name` varchar(50) NOT NULL COMMENT 'Worker名称',
  `execute` varchar(50) NOT NULL COMMENT '要执行的worker类',
  `args` text NOT NULL COMMENT '任务参数',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='worker任务';

-- 正在导出表  dtmall.ylh_swoole_worker 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `ylh_swoole_worker` DISABLE KEYS */;
REPLACE INTO `ylh_swoole_worker` (`id`, `atime`, `etime`, `status`, `name`, `execute`, `args`, `remark`) VALUES
	(5, '2016-10-31 15:50:06', '2016-11-01 13:56:25', 1, '唐人街相关队列任务', 'Trj', '', '');
/*!40000 ALTER TABLE `ylh_swoole_worker` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
